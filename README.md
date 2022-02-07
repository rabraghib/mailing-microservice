# Mailing Microservice

<p>
    <a href="https://github.com/rabraghib/mailing-microservice/blob/main/LICENSE">
        <img alt="GitHub" src="https://img.shields.io/github/license/rabraghib/mailing-microservice">
    </a>
    <a href="https://codecov.io/gh/rabraghib/mailing-microservice">
        <img src="https://codecov.io/gh/rabraghib/mailing-microservice/branch/main/graph/badge.svg?token=AUNN6I95IA"/>
    </a>
    <a href="https://github.com/rabraghib/mailing-microservice/actions/workflows/tests.yml">
        <img alt="Tests" src="https://github.com/rabraghib/mailing-microservice/actions/workflows/tests.yml/badge.svg">
    </a>
</p>

Solution for [Moroccan PHPers](https://www.facebook.com/groups/moroccanphpers/)'s February 2022 Challenge by ***Rabyâ Raghib (<rabraghib@gmail.com>)***.

It mainly consists of:
- a php app that provides 3 endpoints
  - `POST /send`: request sending an email. It expects a payload like:
    ```
    {
        "sender": "sender@exemple.com", // the email address of the sender.
        "recipient": "recipient@exemple.com", // the email address of the receiver.
        "message": "Hello World!", // email body as HTML format.
        "priority": 4 // oprtional, default to 0
    }
    ```
  - `GET /status/{id}`: get the status of request with id `{id}`, the response:
    ```
    {
        "request_id": "24E69C73-D9D6-7B93-77F3-82D7968E8DED",
        "status": "processing",
        "priority": 3
    }
    ```
  - `POST /status-webhook`: to handle webhook calls it expect:
    ```
    {
        "ID": "The id we sent in the first request",
        "STATUS": "DELIVERED" | "REJECTED" | "FAILED"
    }
    ```
- a worker for submitting emails (that failed submitting in the `/send` request) for delivery taking in consideration prioritization

  Its background job that keep checking & submitting queued emails every `$WORKER_PERIOD_SECONDS` or can also run only once and exit. See [Project setup](#project-setup) Section.

## Architecture diagram:
<img width="1284" alt="Architecture Diagram" src="https://user-images.githubusercontent.com/49442862/152674921-3f7cfa4a-8fdd-4b62-b91f-2cb8db4b0eb4.png">

## Project setup:
*Please ensure you set the env variables before running the project. either in `.env`, `.env.local` (not committed) or via system environment variables*
```shell
# run both app & worker services
docker-compose up -d
```
or instead you can run just the app via:
```shell
docker-compose up -d nginx
```
you can try submitting queued emails via the worker with:
```shell
# To keep checking & submitting queued emails every $WORKER_PERIOD_SECONDS
docker-compose up -d worker-service 
# Submit all current queued emails and exist
docker-compose run worker-service " " # clearing default CMD (--serve)
```

## Assumptions:
List of assumptions if you had to take any.
- The webhook call is made via a `POST` request to `/status-webhook`
- An env variable will be set with the mail delivery service `API_BASE`
- This is a private api that can be accessed only by trusted (authorized) services

## Improvements:
What would you have added if you had more time.
- [X] Implement prioritizing logic ([#2](https://github.com/rabraghib/mailing-microservice/issues/2))
- [X] Write some unit tests for each endpoint ([#3](https://github.com/rabraghib/mailing-microservice/issues/3))
- [X] GitHub action workflows for tests and release ([#6](https://github.com/rabraghib/mailing-microservice/issues/6))
- [ ] Add endpoint `PATCH /que-emails/{id}` to add ability to update not submitted emails ([#7](https://github.com/rabraghib/mailing-microservice/issues/7))
