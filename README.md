# Mailing Microservice

Solution for [Moroccan PHPers](https://www.facebook.com/groups/moroccanphpers/)'s February 2022 Challenge by ***Rabyâ Raghib (<rabraghib@gmail.com>)***.

It mainly consists of:
- a php app that provides the 2 endpoints (`/status/{id}`,`/send`) and handles the webhook calls in `/status-webhook`
- a worker for submitting emails for delivery taking in consideration prioritization

## Architecture diagram:
<img width="1297" alt="Architecture Diagram" src="https://user-images.githubusercontent.com/49442862/152660059-60c7a2c9-fcdd-476b-84af-d746ee457a9c.png">

## Project setup:
*Please ensure you set the env variables before running the project. either in `.env`, `.env.local` (not committed) or via system environment variables*
```shell
# run both app & worker
docker-compose up -d
```
or instead you can run just the app via:
```shell
docker-compose run app
```
you can try submitting queued emails via the worker with:
```shell
# Submit all queued emails and exist
docker-compose run worker
# To keep checking & submitting queued emails every $WORKER_PERIOD_SECONDS
docker-compose run worker --serve
```

## Assumptions:
List of assumptions if you had to take any.
- The webhook call is made via a `POST` request to `/status-webhook`
- An env variable will be set with the mail delivery service `API_BASE`
- This is a private api that can be accessed only by trusted (authorized) services

## Improvements:
What would you have added if you had more time.
- [ ] Implement prioritizing logic ([#2](https://github.com/rabraghib/mailing-microservice/issues/2))
- [ ] Write some unit tests for each endpoint ([#3](https://github.com/rabraghib/mailing-microservice/issues/2))
