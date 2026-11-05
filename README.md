# identity-link-symfony-example

A Symfony 8.1 application that signs users in with a local [Identity Link](https://github.com/sgoranov/php-identity-link) server through [`drenso/symfony-oidc-bundle`](https://github.com/Drenso/symfony-oidc). The bundle provides the login itself, so the app has no authenticator of its own: only configuration, a user provider, and a few routes.

It shows:

- signing in with the authorization code flow, state, nonce and PKCE (S256), validating the ID token, and taking the user from Identity Link's UserInfo endpoint;
- returning to the page the user asked for;
- a sign-in failed page with a "Sign in again" link when the sign-in is cancelled, expired, already used (e.g. a reloaded callback page), or rejected;
- signing out of the application, which is all it takes, since Identity Link keeps no login session;
- a session-only user with `ROLE_USER`, without a database.

## Requirements

- Docker with Compose.
- A local Identity Link from [identity-link-docker](https://github.com/sgoranov/identity-link-docker), reachable as `https://example.com` from the browser and from the app container. `docker-compose.yaml` maps `example.com` to the Docker host and mounts the host's CA bundle, which must contain the CA of Identity Link's certificate (e.g. mkcert's). On the host, `example.com` must resolve to the local Identity Link, e.g. through `/etc/hosts`.

## Setup

1. Register the app in Identity Link. In the identity-link-docker checkout:

   ```bash
   bin/create-client.sh \
     --audience http://localhost:8000 \
     --redirect-uri http://localhost:8000/connect/identity-link/check \
     --name identity-link-symfony-example
   ```

   The script prints the client ID and secret once. It also adds the `refresh_token` grant, which this app does not use, although the bundle keeps the refresh token in the session.

2. Put them into `.env.local`, which git ignores:

   ```dotenv
   IDENTITY_LINK_CLIENT_ID=...
   IDENTITY_LINK_CLIENT_SECRET=...
   ```

3. Install the dependencies and start the app:

   ```bash
   docker compose run --rm symfony-example composer install
   docker compose up --build
   ```

4. Open http://localhost:8000/. It redirects to Identity Link; after signing in, the home page shows the user's ID (`sub`), email, and roles.

## Configuration

| Variable | Set in | Description |
|---|---|---|
| `IDENTITY_LINK_DISCOVERY_URL` | `.env` | Identity Link's OpenID Connect discovery document; `https://example.com/.well-known/openid-configuration` for the local one. The bundle caches it for an hour. |
| `IDENTITY_LINK_CLIENT_ID` | `.env.local` | Printed by `create-client.sh`. |
| `IDENTITY_LINK_CLIENT_SECRET` | `.env.local` | Printed by `create-client.sh`. Never commit it. |

The redirect URI registered in Identity Link must be exactly `http://localhost:8000/connect/identity-link/check`, the `connect_identity_link_check` route.

## Where things are

- `config/packages/drenso_oidc.yaml`: the OIDC client, with PKCE turned on.
- `config/packages/security.yaml`: the firewall with the bundle's `oidc` listener, logout, and access control.
- `src/Controller/SecurityController.php`: the sign-in start, callback, sign-in failed, and signed-out routes.
- `src/Security/`: the session-only `User` and `UserProvider`.
- `templates/`: the home, sign-in failed, and signed-out pages.

## License

This application is licensed under the [MIT License](LICENSE).
