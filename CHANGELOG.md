# Changelog

All notable changes to `laravel-odk-link` will be documented in this file.

## v0.3.1 - 2022-11-21

Updates process submission endpoint to require a route name. (Easier / more versatile than a url).

## v0.3 - Submission Processing - 2022-11-21

To postpone the challenge of writing a generalised submission processing script, this update includes a config variable `config('submission.process_endpoint')`, which can be set per-project. This end point should:

- accept a POST request;
- get the submission from the `submission_id` in the body of the request.
- Do whatever the project needs to do with that submission to consider it "processed". (e.g, extract the content and put data into some database tables, send email alerts, trigger other scripts etc.
