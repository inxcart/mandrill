# Mandrill
![Mandrill](logo.png)

Mandrill module for thirty bees

## About

This module overrides the Mail class and routes all transactional mails through Mandrill.
As long as your Mandrill API Key is correct you no longer have to configure the SMTP settings.
Those will be ignored after installing this module.

## Known limitations

- Extra mandrill params passed are ignored at the moment. We are working on supporting more Mandrill features.
- The module always sends Mails asynchronously. Meaning, it will never wait for Mandrill's status, but just dumps the mail into their API instead.
This will be configurable in a future version.

## Instructions

Install the module and enter your Mandrill API key (can be found on [this page](https://mandrillapp.com/settings/index)).

## Requirements
- thirty bees 1.0.0+
- PHP 5.5+
- PHP cURL extension

## Troubleshooting

If the module cannot be installed due to the overrides already being in use, then you will have to either:
- Remove the previous overrides
- Find a way to merge the Mandrill overrides (they can be found in this module folder [`/overrides/classes`])
