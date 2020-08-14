## Money Transfer API via Paystack

A restful API that allows authorised users make money transfer via Paystack payment gateway.

The following assumptions have been made on the business logic:

- Stateless authentication with JSON Web Tokens
- users can make money transfer to a new account, account is automatically added as a recipient/beneficiary for subsequent transfer.
- users are required to be authenticated to perform operations that require authentication.

Endpoints exposed to clients include:

- Create new account (Register)
- Login to generate token
- Initiate send money to new account
- Finalize transfer via OTP
- Verify transfer (check transfer status)
- Fetch recipients list
- Transfer to recipient
- Fetch transfer list with filter options

## Requirements

Laravel (v5.6)/(PHP v^7.1.3), MySQL.

## Getting started

Clone the repository

```
git clone git@github.com:phalconVee/trybrass-money-transfer-api.git .
```

After that go into the project directory and find .env file. Replace the PAYSTACK_SECRET_KEY, PAYSTACK_PUBLIC_KEY, and other environment variables inside the file to enable the app to run well.

Then install the dependencies.

```
composer install
```

## Consideration for Testing

The sample live API URL is available here: http://localhost:4000

Also, the postman collection is available publicly via 
[POSTMAN](https://nodejs.org/en/)

The database migration is available in the migrations folder.
