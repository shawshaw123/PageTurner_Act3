// Swagger UI for the admin "API Docs" page (resources/views/admin/api-docs.blade.php).
import SwaggerUI from 'swagger-ui-dist/swagger-ui-es-bundle.js';
import 'swagger-ui-dist/swagger-ui.css';

const element = document.getElementById('swagger-ui');

const ui = SwaggerUI({
    url: element.dataset.specUrl,
    domNode: element,
    deepLinking: true,
    docExpansion: 'list',
    defaultModelsExpandDepth: 0,
    displayRequestDuration: true, // shows how long each request took
    tryItOutEnabled: true,
    persistAuthorization: true,
    requestInterceptor: (request) => {
        request.headers.Accept = 'application/json';
        // API requests use the bearer token only, never the admin's session cookie.
        request.credentials = 'omit';
        return request;
    },
    responseInterceptor: (response) => {
        // After a successful POST /auth/token, authorise the rest of the page with the new token.
        if (response.status === 201 && response.url.endsWith('/auth/token') && response.body?.token) {
            ui.preauthorizeApiKey('bearerAuth', response.body.token);
        }
        return response;
    },
});
