<?php

namespace Stats4sd\OdkLink\Commands;

use Stats4sd\OdkLink\Imports\XlsformImport;
use Carbon\Carbon;
use Faker\Generator;
use Faker\Provider\DateTime;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ParseError;
use Stats4sd\OdkLink\Models\OdkProject;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\Xlsform;
use Illuminate\Console\Command;
use Stats4sd\OdkLink\Models\XlsformVersion;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Stats4sd\OdkLink\Services\SubmissionGenerator;

/**
 * Command to generate a fake submission in the submissions table for the selected ODK form.
 * - It reads the XLS file and generates a submission in the correct format, parsing the calculations / XPath expressions where possible.
 * - It can handle any number of nested repeat groups, and will create correctly formatted nested JSON `content`.
 *
 * TODO: add capacity for reading constraint columns.
 * TODO: add capacity for reading relevant columns.
 *
 * TODO: add capacity for user to specify output type or ranges for text, integer, decimal, calculate fields within the XLS file itself.
 * TODO: extract into a package (seperate to Kobo Link? Or part of it?)
 */
class GenerateSubmissionRecords extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odk:generate-subs {xlsformVersion?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses the xlsform definition to generate fake submission records for a given team_xlsform';

    /**
     * @var Generator
     */
    protected Generator $faker; // Uses faker to generate random values (similar to Model Factories);
    protected XlsformVersion $xlsformVersion; // The xlsform version to generate a submission for.
    protected ?Collection $variables; // Collection containing all the variables pulled from the xlsform definition (survey sheet)
    protected ?Collection $choices; // Collection containing all the choices pulled from the xlsform definition (choices sheet)
    protected Collection $content; // Collection containing the content of the full submission
    protected OdkLinkService $odkLinkService;

    /**
     * Create a new command instance.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function __construct(OdkLinkService $odkLinkService, $variables = null, $choices = null)
    {
        parent::__construct();

        // if the ODK form variables and choices are stored in the database, add them in the construct. Otherwise, the method will create these collections from the $xlsform->xlsfile.
        $this->variables = $variables;
        $this->choices = $choices;

        $this->odkLinkService = $odkLinkService;

        // create an instance of faker to use when generating the response values;
        $this->faker = $this->withFaker();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws BindingResolutionException
     */
    public function handle()
    {
        if ($this->argument('xlsformVersion')) {
            // get the correct xlsform
            $this->xlsformVersion = Xlsform::find($this->argument('xlsform'));
        } else {
            // ask user which team / form to use:
            $odkProject = $this->choice('Which Form Owner do you want to use?', OdkProject::all()->pluck('name', 'id')->toArray());
            $xlsform = $this->choice('Which xlsform do you want to use?', OdkProject::where('name', $odkProject)->first()->xlsforms->pluck('title', 'id')->toArray());
            dump($xlsform);
            $xlsform = Xlsform::all()->where('title', $xlsform)->first();

            if ($xlsform->xlsformVersions()->count() > 0) {
                $xlsformVersion = $this->choice(
                    'Which version of the form do you want to use?',
                    $xlsform->xlsformVersions->pluck('version', 'id')->toArray()
                );
                $this->xlsformVersion = XlsformVersion::where('version', $xlsformVersion)->where('xlsform_id', $xlsform->id)->first();
            } else {
                $this->info('The chosen form does not have any deployed versions. A new version will be generated to link these submissions to.');
                $this->xlsformVersion = $this->odkLinkService->createNewVersion($xlsform, Carbon::now()->toDateTimeString());
            }
        }

        $this->info('you chose the form ... ' . $this->xlsformVersion->xlsform->title);
        $this->info('and the version ... ' . $this->xlsformVersion->version);
        if (!$this->confirm('Do you wish to continue to generate submissions for this form?', true)) {
            $this->info('exiting!');
            return COMMAND::INVALID;
        }

        // get survey + choices sheet data;
        // returns nested collection:
        //        [
        //            'survey' => Collection of rows;
        //            'choices' => Collection of rows;
        //        ]
        $collection = Excel::toCollection(new XlsformImport, Storage::disk(config('odk-link.storage.xlsforms'))->path($this->xlsformVersion->xlsfile));

        $variables = collect([]);

        // get list of types and names from survey:
        foreach ($collection['survey'] as $index => $variable) {
            $variables->push([
                'index' => $index,
                'type' => $variable['type'],
                'name' => $variable['name'],
                'appearance' => $variable['appearance'] ?? null, // to check if select is from an external csv (i.e. a database table)
                'repeat_count' => $variable['repeat_count'] ?? null, // for generating the appropriate number of repeats;
                'calculation' => $variable['calculation'] ?? null,
                // Note yet used
                // 'relevant' => $variable['relevant'],
                // 'constraint' => $variable['constraint'],
            ]);
        }

        // get choice lists
        $this->choices = $collection['choices']->groupBy('list_name');
        unset($this->choices[""]);

        // $choiceNames = $this->choices->map(fn($choice) => $choice->pluck('name'));

        // process variables;
        $generator = (new SubmissionGenerator($this->xlsformVersion, $variables, $this->choices, collect([])));

        $entry = $generator->processVariablesSequentially();

        // add 'odk' metadata
        if (Submission::count() > 0) {
            $submissionIdMax = Submission::orderBy('id', 'desc')->take(1)->get()->first()->id;
        } else {
            $submissionIdMax = 0;
        }

        $entry['_id'] = $submissionIdMax + 1000000000;
        $entry['formhub/uuid'] = $this->faker->uuid();
        $entry['meta/instanceID'] = 'uuid:' . $entry['formhub/uuid'];
        $entry['_status'] = 'fake submission';
        $entry['_submission_time'] = Carbon::now()->format('Y-m-d') . 'T' . Carbon::now()->format('H-m-s');
        $entry['_tags'] = [];
        $entry['_notes'] = [];
        $entry['_attachments'] = [];
        $entry['_validation_status'] = [];
        $entry['submitted_by'] = "Custom generation code";

        // dump($submission);
        $this->xlsformVersion->submissions()->create([
            'id' => $entry['_id'],
            'uuid' => $entry['formhub/uuid'],
            'submitted_at' => $entry['_submission_time'],
            'content' => $entry->toJson(),
        ]);


        return Command::SUCCESS;
    }


    /**
     * Get a new Faker instance.
     *
     * @return Generator
     * @throws BindingResolutionException
     */
    protected function withFaker(): Generator
    {
        return Container::getInstance()->make(Generator::class);
    }
}
