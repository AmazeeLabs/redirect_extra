# Redirect Extra

Optional configuration for the Redirect module (WIP).

- Add validation for the 'to' path to prevent 404 errors (internal and/or external)
- Warn or convert the destination while creating a redirect chain
- Permissions to use redirect status (301, 302, ...)


## Configuration

/admin/config/search/redirect_extra/settings

## Roadmap

- Bulk tasks for detecting and fixing: redirect chains, 404 redirects
- Implement distinction between API and Form scope,
  to allow different behaviours for bulk (API) and single tasks (Form)
- Unit tests
