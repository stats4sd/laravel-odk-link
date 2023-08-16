
<h3>DRAFT FORM TESTING</h3>
<p>Your XLSform file has been uploaded to ODK Central. You can review the draft using ODK Collect or Enketo. We recommend previewing the form with the same tool that will be used for data collection, because Enketo and ODK Collect render the same form in quite different ways.</p>


<p>Note that this is not intended to be used for any real data collection. No submissions are kept from these forms. You may also find the form does not work properly if there are missing media files or datasets.</p>

<h3>Preview in ODK Collect</h3>

<p>In ODK Collect, go to 'add new project' and then scan the QR code below. THis will create a new project with <b>only</b> this form. Once you have finished testing the form, you can delete that entire project from ODK Collect to keep your project list tidy.

<div class='my-4 mx-3 d-flex justify-content-start'>
    {{ QrCode::size(200)->generate($xlsformTemplate->draft_qr_code_string) }}
</div>
<h3 class='mt-4'>Preview in Enketo</h3>
<a href="{{ config(' odk-link.odk.url') ."/-/{$xlsformTemplate->enketo_draft_url}" }}" target='_blank'>Preview the form in Enketo webforms here</a>.
