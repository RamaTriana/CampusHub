<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

ob_start();

require __DIR__ . '/../app/bootstrap.php';

use CampusHub\Core\Controller;
use CampusHub\Core\CsrfToken;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Router;

final class RouterTestController extends Controller
{
    public function ok(Request $request): Response
    {
        return Response::text('ok');
    }
}

$passed = 0;
$failed = 0;

$assert = static function (bool $condition, string $name) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        echo "PASS $name\n";
        return;
    }

    $failed++;
    fwrite(STDERR, "FAIL $name\n");
};

$makeRouter = static function (): Router {
    $router = new Router();
    $router->registerMiddleware('auth', static fn () => new \CampusHub\Http\Middleware\AuthMiddleware());
    $router->registerMiddleware('guest', static fn () => new \CampusHub\Http\Middleware\GuestMiddleware());
    $router->registerMiddleware(
        'role',
        static fn (string ...$roles) => new \CampusHub\Http\Middleware\RoleMiddleware($roles)
    );
    $router->registerMiddleware('csrf', static fn () => new \CampusHub\Http\Middleware\CsrfMiddleware());

    return $router;
};

$handler = [RouterTestController::class, 'ok'];

try {
    $request = new Request('GET', '/mvc//ready/?source=test');
    $assert($request->path() === '/mvc/ready', 'normalizes duplicate/trailing slashes and query string');
} catch (Throwable) {
    $assert(false, 'normalizes duplicate/trailing slashes and query string');
}

try {
    new Request('GET', '/%2e%2e/config');
    $assert(false, 'rejects traversal paths');
} catch (InvalidArgumentException) {
    $assert(true, 'rejects traversal paths');
}
$fileRequest = new Request('POST', '/upload', [], [], [], [
    'foto' => ['name' => 'photo.png', 'tmp_name' => 'temporary', 'error' => UPLOAD_ERR_OK],
]);
$assert(
    ($fileRequest->file('foto')['name'] ?? null) === 'photo.png',
    'request exposes uploaded file data'
);

$router = $makeRouter();
$router->get('/known', $handler);
$router->post('/submit', $handler);
$assert($router->dispatch(new Request('GET', '/known'))->status() === 200, 'dispatches GET route');
$assert($router->dispatch(new Request('POST', '/submit'))->status() === 200, 'dispatches POST route');
$assert($router->dispatch(new Request('GET', '/missing'))->status() === 404, 'returns 404 for unknown route');
$methodResponse = $router->dispatch(new Request('PUT', '/known'));
$assert(
    $methodResponse->status() === 405 && ($methodResponse->headers()['Allow'] ?? '') === 'GET',
    'returns 405 and Allow header for unsupported method'
);

$router = $makeRouter();
$router->get('/auth', $handler, ['auth']);
$guestResponse = $router->dispatch(new Request('GET', '/auth'));
$assert(
    $guestResponse->status() === 302 && ($guestResponse->headers()['Location'] ?? '') === '/login',
    'auth middleware redirects unauthenticated visitor'
);

$router = $makeRouter();
$router->get('/guest', $handler, ['guest']);
$authenticatedSession = ['is_login' => true, 'user_id' => 12, 'role' => ROLE_STUDENT];
$guestOnlyResponse = $router->dispatch(new Request('GET', '/guest', [], [], $authenticatedSession));
$assert(
    $guestOnlyResponse->status() === 302
        && ($guestOnlyResponse->headers()['Location'] ?? '') === '/student/dashboard',
    'guest middleware redirects signed-in student to the official dashboard route'
);
$unknownGuestResponse = $router->dispatch(new Request(
    'GET',
    '/guest',
    [],
    [],
    ['is_login' => true, 'user_id' => 99, 'role' => 'Unknown']
));
$assert(
    $unknownGuestResponse->status() === 302
        && ($unknownGuestResponse->headers()['Location'] ?? '') === '/login'
        && !isset($_SESSION['is_login']),
    'guest middleware clears invalid role session without redirect loop'
);

$router = $makeRouter();
$router->get('/admin', $handler, ['auth', ['role', ROLE_ADMIN]]);
$wrongRoleResponse = $router->dispatch(new Request(
    'GET',
    '/admin',
    [],
    [],
    ['is_login' => true, 'user_id' => 15, 'role' => ROLE_STUDENT]
));
$assert(
    $wrongRoleResponse->status() === 302
        && ($wrongRoleResponse->headers()['Location'] ?? '') === '/student/dashboard',
    'role middleware redirects authenticated user to their own dashboard'
);
$roleGuestResponse = $router->dispatch(new Request('GET', '/admin'));
$assert(
    $roleGuestResponse->status() === 302
        && ($roleGuestResponse->headers()['Location'] ?? '') === '/login',
    'role middleware redirects unauthenticated visitor'
);
$unknownRoleResponse = $router->dispatch(new Request(
    'GET',
    '/admin',
    [],
    [],
    ['is_login' => true, 'user_id' => 15, 'role' => 'Unknown']
));
$assert($unknownRoleResponse->status() === 403, 'role middleware denies unknown role');

$router = $makeRouter();
$router->post('/submit', $handler, ['csrf']);
$invalidCsrfResponse = $router->dispatch(new Request('POST', '/submit', [], ['_csrf_token' => 'bad'], [
    '_mvc_csrf_token' => str_repeat('a', 64),
]));
$assert($invalidCsrfResponse->status() === 403, 'CSRF middleware rejects an invalid token');
$validToken = str_repeat('b', 64);
$validCsrfResponse = $router->dispatch(new Request(
    'POST',
    '/submit',
    [],
    ['_csrf_token' => $validToken],
    ['_mvc_csrf_token' => $validToken]
));
$assert($validCsrfResponse->status() === 200, 'CSRF middleware accepts a matching token');

$router = $makeRouter();
$router->get('/page', $handler, ['csrf']);
$assert($router->dispatch(new Request('GET', '/page'))->status() === 200, 'CSRF middleware allows safe GET');

$renderedView = \CampusHub\Core\View::render('home/index', ['title' => '<CampusHub>']);
$assert(
    str_contains($renderedView, '&lt;CampusHub&gt;'),
    'view renders and escapes dynamic HTML'
);
$categoryXssView = \CampusHub\Core\View::render('admin/categories/index', [
    'userName' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'photo' => 'default.png',
    'categories' => [[
        'id_kategori' => 1,
        'nama_kategori' => '<script>alert(1)</script>',
        'created_at' => '2026-01-01 00:00:00',
    ]],
]);
$assert(
    str_contains($categoryXssView, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && !str_contains($categoryXssView, '<script>alert(1)</script>'),
    'category list escapes database values to prevent XSS'
);
$assert(
    class_exists(\CampusHub\Controllers\HomeController::class),
    'namespace autoloader resolves application controllers'
);
$passwordHash = password_hash('CampusHub-Test-Password-123!', PASSWORD_DEFAULT);
$assert(
    $passwordHash !== 'CampusHub-Test-Password-123!'
        && password_verify('CampusHub-Test-Password-123!', $passwordHash),
    'password hashes verify without storing plaintext'
);
$assert(
    str_contains(\CampusHub\Core\View::csrfField(), 'name="_csrf_token"'),
    'CSRF field helper creates a token input'
);

$authRouter = $makeRouter();
$registerAuthRoutes = require __DIR__ . '/../routes/web.php';
$registerAuthRoutes($authRouter);

$adminCategorySession = [
    'is_login' => true,
    'user_id' => 1,
    'nama' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'foto' => 'default.png',
];
$categoryGuest = $authRouter->dispatch(new Request('GET', '/admin/kategori'));
$assert(
    $categoryGuest->status() === 302
        && ($categoryGuest->headers()['Location'] ?? '') === '/login',
    'category routes redirect guests to login'
);
$legacyCategoryIndexGuest = $authRouter->dispatch(new Request('GET', '/admin/kategori/index.php'));
$assert(
    $legacyCategoryIndexGuest->status() === 302
        && ($legacyCategoryIndexGuest->headers()['Location'] ?? '') === '/login',
    'legacy category index adapter reaches the protected MVC route'
);
$categoryWrongRole = $authRouter->dispatch(new Request(
    'GET',
    '/admin/kategori',
    [],
    [],
    ['is_login' => true, 'user_id' => 2, 'role' => ROLE_STUDENT]
));
$assert(
    $categoryWrongRole->status() === 302
        && ($categoryWrongRole->headers()['Location'] ?? '') === '/student/dashboard',
    'category routes redirect non-admin roles to their own dashboard'
);
$categoryUnknownRole = $authRouter->dispatch(new Request(
    'GET',
    '/admin/kategori',
    [],
    [],
    ['is_login' => true, 'user_id' => 3, 'role' => 'Unknown']
));
$assert($categoryUnknownRole->status() === 403, 'category routes deny unknown roles');
$categoryCreateForm = $authRouter->dispatch(new Request(
    'GET',
    '/admin/kategori/tambah',
    [],
    [],
    $adminCategorySession
));
$assert(
    $categoryCreateForm->status() === 200
        && str_contains($categoryCreateForm->body(), 'name="_csrf_token"')
        && str_contains($categoryCreateForm->body(), 'action="/admin/kategori/simpan"')
        && str_contains($categoryCreateForm->body(), 'maxlength="100"'),
    'Admin can open the category form with CSRF protection and schema validation'
);
$categoryEditInvalidId = $authRouter->dispatch(new Request(
    'GET',
    '/admin/kategori/edit?id=invalid',
    [],
    [],
    $adminCategorySession
));
$assert($categoryEditInvalidId->status() === 404, 'category edit rejects an invalid identifier');
$invalidCategoryCsrf = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/simpan',
    [],
    ['nama_kategori' => 'Test'],
    $adminCategorySession
));
$assert($invalidCategoryCsrf->status() === 403, 'category writes reject a missing CSRF token');
$invalidCategoryUpdateCsrf = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/perbarui',
    [],
    ['id' => '1', 'nama_kategori' => 'Category'],
    $adminCategorySession
));
$assert($invalidCategoryUpdateCsrf->status() === 403, 'category updates reject a missing CSRF token');
$categoryWriteMethod = $authRouter->dispatch(new Request(
    'GET',
    '/admin/kategori/simpan',
    [],
    [],
    $adminCategorySession
));
$assert(
    $categoryWriteMethod->status() === 405
        && ($categoryWriteMethod->headers()['Allow'] ?? '') === 'POST',
    'category creation endpoint only accepts POST'
);
$categoryCsrf = \CampusHub\Core\CsrfToken::get();
$categorySession = $_SESSION;
$categorySession['is_login'] = true;
$categorySession['user_id'] = 1;
$categorySession['role'] = ROLE_ADMIN;
$invalidCategoryName = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/simpan',
    [],
    ['_csrf_token' => $categoryCsrf, 'nama_kategori' => '   '],
    $categorySession
));
$categoryValidationFlash = \CampusHub\Core\Flash::pull();
$assert(
    $invalidCategoryName->status() === 302
        && ($invalidCategoryName->headers()['Location'] ?? '') === '/admin/kategori/tambah'
        && ($categoryValidationFlash['type'] ?? null) === 'Danger',
    'category creation rejects an empty name before database access'
);
$longCategoryName = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/simpan',
    [],
    ['_csrf_token' => $categoryCsrf, 'nama_kategori' => str_repeat('a', 101)],
    $categorySession
));
\CampusHub\Core\Flash::pull();
$assert(
    $longCategoryName->status() === 302
        && ($longCategoryName->headers()['Location'] ?? '') === '/admin/kategori/tambah',
    'category creation rejects names longer than the database column'
);
$invalidCategoryUpdate = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/perbarui',
    [],
    ['_csrf_token' => $categoryCsrf, 'id' => 'invalid', 'nama_kategori' => 'Category'],
    $categorySession
));
$assert($invalidCategoryUpdate->status() === 404, 'category update rejects an invalid identifier');
$categoryDeleteRoute = $authRouter->dispatch(new Request(
    'POST',
    '/admin/kategori/hapus',
    [],
    ['_csrf_token' => $categoryCsrf],
    $categorySession
));
$assert($categoryDeleteRoute->status() === 404, 'category deletion rejects requests without a valid category ID');

$adminOrganizationSession = [
    'is_login' => true,
    'user_id' => 1,
    'nama' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'foto' => 'default.png',
    '_mvc_csrf_token' => $categoryCsrf,
];
foreach ([
    '/admin/organisasi',
    '/admin/organisasi/index.php',
    '/admin/organisasi/tambah',
    '/admin/organisasi/edit?id=1',
] as $organizationPath) {
    $organizationGuest = $authRouter->dispatch(new Request('GET', $organizationPath));
    $assert(
        $organizationGuest->status() === 302
            && ($organizationGuest->headers()['Location'] ?? '') === '/login',
        'organization route redirects guests to login: ' . $organizationPath
    );
}
$organizationWrongRole = $authRouter->dispatch(new Request(
    'GET',
    '/admin/organisasi',
    [],
    [],
    ['is_login' => true, 'user_id' => 2, 'role' => ROLE_COMMITTEE]
));
$assert(
    $organizationWrongRole->status() === 302
        && ($organizationWrongRole->headers()['Location'] ?? '') === '/committee/dashboard',
    'organization routes redirect non-admin roles to their dashboard'
);
$organizationUnknownRole = $authRouter->dispatch(new Request(
    'GET',
    '/admin/organisasi',
    [],
    [],
    ['is_login' => true, 'user_id' => 3, 'role' => 'Unknown']
));
$assert($organizationUnknownRole->status() === 403, 'organization routes deny unknown roles');
$organizationCreateForm = $authRouter->dispatch(new Request(
    'GET',
    '/admin/organisasi/tambah',
    [],
    [],
    $adminOrganizationSession
));
$assert(
    $organizationCreateForm->status() === 200
        && str_contains($organizationCreateForm->body(), 'name="_csrf_token"')
        && str_contains($organizationCreateForm->body(), 'enctype="multipart/form-data"')
        && str_contains($organizationCreateForm->body(), 'name="nama_organisasi"')
        && str_contains($organizationCreateForm->body(), 'name="deskripsi"')
        && str_contains($organizationCreateForm->body(), 'name="logo"'),
    'Admin organization form includes schema fields, multipart encoding, and CSRF'
);
$invalidOrganizationId = $authRouter->dispatch(new Request(
    'GET',
    '/admin/organisasi/edit?id=invalid',
    [],
    [],
    $adminOrganizationSession
));
$assert($invalidOrganizationId->status() === 404, 'organization edit rejects an invalid identifier');
$missingOrganizationCsrf = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    ['nama_organisasi' => 'Campus Club', 'deskripsi' => 'Description'],
    $adminOrganizationSession
));
$assert($missingOrganizationCsrf->status() === 403, 'organization creation rejects missing CSRF');
$invalidOrganizationCsrf = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => str_repeat('0', 64),
        'nama_organisasi' => 'Campus Club',
        'deskripsi' => '',
    ],
    $adminOrganizationSession
));
$assert($invalidOrganizationCsrf->status() === 403, 'organization creation rejects an invalid CSRF token');
$missingOrganizationUpdateCsrf = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/perbarui',
    [],
    ['id' => '1', 'nama_organisasi' => 'Campus Club', 'deskripsi' => 'Description'],
    ['is_login' => true, 'user_id' => 1, 'role' => ROLE_ADMIN]
));
$assert($missingOrganizationUpdateCsrf->status() === 403, 'organization update rejects missing CSRF');
$organizationWrongMethod = $authRouter->dispatch(new Request(
    'GET',
    '/admin/organisasi/simpan',
    [],
    [],
    $adminOrganizationSession
));
$assert(
    $organizationWrongMethod->status() === 405
        && ($organizationWrongMethod->headers()['Allow'] ?? '') === 'POST',
    'organization create endpoint accepts POST only'
);
$invalidOrganizationName = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => $categoryCsrf,
        'nama_organisasi' => '  ',
        'deskripsi' => '',
    ],
    $adminOrganizationSession
));
$organizationValidationFlash = \CampusHub\Core\Flash::pull();
$assert(
    $invalidOrganizationName->status() === 302
        && ($invalidOrganizationName->headers()['Location'] ?? '') === '/admin/organisasi/tambah'
        && ($organizationValidationFlash['type'] ?? null) === 'Danger',
    'organization creation rejects an empty name before database access'
);
$longOrganizationName = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => $categoryCsrf,
        'nama_organisasi' => str_repeat('a', 101),
        'deskripsi' => '',
    ],
    $adminOrganizationSession
));
\CampusHub\Core\Flash::pull();
$assert(
    $longOrganizationName->status() === 302
        && ($longOrganizationName->headers()['Location'] ?? '') === '/admin/organisasi/tambah',
    'organization creation rejects a name exceeding VARCHAR(100)'
);
$invalidOrganizationDescription = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => $categoryCsrf,
        'nama_organisasi' => 'Campus Club',
        'deskripsi' => ['invalid'],
    ],
    $adminOrganizationSession
));
\CampusHub\Core\Flash::pull();
$assert(
    $invalidOrganizationDescription->status() === 302,
    'organization creation rejects a non-string description'
);
$oversizedOrganizationDescription = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => $categoryCsrf,
        'nama_organisasi' => 'Campus Club',
        'deskripsi' => str_repeat('a', 65536),
    ],
    $adminOrganizationSession
));
\CampusHub\Core\Flash::pull();
$assert(
    $oversizedOrganizationDescription->status() === 302,
    'organization creation rejects a description beyond MySQL TEXT capacity'
);
$invalidOrganizationUpload = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/simpan',
    [],
    [
        '_csrf_token' => $categoryCsrf,
        'nama_organisasi' => 'Campus Club',
        'deskripsi' => '',
    ],
    $adminOrganizationSession,
    ['logo' => [
        'name' => 'logo.php',
        'tmp_name' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'not-an-upload',
        'error' => UPLOAD_ERR_OK,
    ]]
));
$organizationUploadFlash = \CampusHub\Core\Flash::pull();
$assert(
    $invalidOrganizationUpload->status() === 302
        && ($organizationUploadFlash['type'] ?? null) === 'Danger',
    'organization upload rejects files that are not genuine HTTP uploads'
);

$logoValidator = new \CampusHub\Models\OrganizationLogo();
$noOrganizationLogo = $logoValidator->validate(null);
$assert(
    $noOrganizationLogo['error'] === null
        && $noOrganizationLogo['temporary_path'] === null,
    'organization logo is optional when no file is submitted'
);
$failedOrganizationUpload = $logoValidator->validate([
    'name' => 'logo.png',
    'tmp_name' => '',
    'error' => UPLOAD_ERR_INI_SIZE,
]);
$assert(
    $failedOrganizationUpload['error'] !== null,
    'organization logo reports PHP upload size failures'
);
$validOrganizationLogoPath = tempnam(sys_get_temp_dir(), 'campushub-logo-');
$spoofedOrganizationLogoPath = tempnam(sys_get_temp_dir(), 'campushub-logo-');
$oversizedOrganizationLogoPath = tempnam(sys_get_temp_dir(), 'campushub-logo-');
$organizationLogoFixtures = [
    $validOrganizationLogoPath,
    $spoofedOrganizationLogoPath,
    $oversizedOrganizationLogoPath,
];
try {
    $image = imagecreatetruecolor(2, 2);
    if (!imagepng($image, $validOrganizationLogoPath)) {
        throw new RuntimeException('Could not create image fixture for tests.');
    }
    imagedestroy($image);
    copy($validOrganizationLogoPath, $spoofedOrganizationLogoPath);
    file_put_contents($oversizedOrganizationLogoPath, str_repeat('x', MAX_UPLOAD_SIZE + 1));

    $validLogoInspection = \CampusHub\Models\OrganizationLogo::inspect(
        $validOrganizationLogoPath,
        'campus.png'
    );
    $assert(
        $validLogoInspection['error'] === null
            && $validLogoInspection['extension'] === 'png',
        'organization logo validator accepts a real PNG under the size limit'
    );
    $spoofedLogoInspection = \CampusHub\Models\OrganizationLogo::inspect(
        $spoofedOrganizationLogoPath,
        'campus.jpg'
    );
    $assert(
        $spoofedLogoInspection['error'] !== null,
        'organization logo validator rejects extension and MIME mismatches'
    );
    $invalidExtensionInspection = \CampusHub\Models\OrganizationLogo::inspect(
        $validOrganizationLogoPath,
        'campus.svg'
    );
    $assert(
        $invalidExtensionInspection['error'] !== null,
        'organization logo validator rejects disallowed extensions'
    );
    $oversizedLogoInspection = \CampusHub\Models\OrganizationLogo::inspect(
        $oversizedOrganizationLogoPath,
        'campus.png'
    );
    $assert(
        $oversizedLogoInspection['error'] !== null,
        'organization logo validator rejects files exceeding 2 MB'
    );
} finally {
    foreach ($organizationLogoFixtures as $fixture) {
        if (is_string($fixture) && is_file($fixture)) {
            unlink($fixture);
        }
    }
}
$organizationXssView = \CampusHub\Core\View::render('admin/organizations/index', [
    'userName' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'photo' => 'default.png',
    'organizations' => [[
        'id_organisasi' => 1,
        'nama_organisasi' => '<script>alert(1)</script>',
        'logo' => '../organization_logo.png',
        'deskripsi' => '<img src=x onerror=alert(1)>',
        'created_at' => '2026-01-01 00:00:00',
    ]],
]);
$assert(
    str_contains($organizationXssView, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && str_contains($organizationXssView, '&lt;img src=x onerror=alert(1)&gt;')
        && !str_contains($organizationXssView, '<script>alert(1)</script>')
        && str_contains($organizationXssView, '/assets/uploads/organisasi/organization_logo.png'),
    'organization list escapes text and constrains logo paths to their basename'
);
$organizationFormView = \CampusHub\Core\View::render('admin/organizations/form', [
    'userName' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'photo' => 'default.png',
    'flash' => null,
    'formAction' => '/admin/organisasi/perbarui',
    'heading' => 'Edit Organisasi',
    'organization' => [
        'id_organisasi' => 9,
        'nama_organisasi' => '<script>alert(1)</script>',
        'logo' => '../organization_logo.png',
        'deskripsi' => '<script>alert(2)</script>',
    ],
]);
$assert(
    str_contains($organizationFormView, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && str_contains($organizationFormView, '&lt;script&gt;alert(2)&lt;/script&gt;')
        && !str_contains($organizationFormView, '<script>alert(2)</script>')
        && str_contains($organizationFormView, 'organization_logo.png'),
    'organization form escapes stored values and safely displays the current logo'
);
$organizationDeleteRoute = $authRouter->dispatch(new Request(
    'POST',
    '/admin/organisasi/hapus',
    [],
    ['_csrf_token' => $categoryCsrf],
    $adminOrganizationSession
));
$assert(
    $organizationDeleteRoute->status() === 404,
    'organization deletion rejects requests without a valid organization ID'
);

$eventReferences = [
    'organizations' => [['id_organisasi' => 4, 'nama_organisasi' => 'Organisasi']],
    'committees' => [['id_user' => 7, 'nama' => 'Panitia Aktif']],
    'categories' => [['id_kategori' => 9, 'nama_kategori' => 'Kategori']],
];
$validEventInput = [
    'id_organisasi' => '4',
    'id_panitia' => '7',
    'id_kategori' => '9',
    'judul' => '  Campus Meetup  ',
    'deskripsi' => '',
    'tanggal' => '2028-02-29',
    'jam' => '09:30',
    'lokasi' => 'Aula Kampus',
    'kuota' => '0',
    'status' => 'Draft',
];
$eventValidator = new \CampusHub\Validation\EventInputValidator();
$validEventResult = $eventValidator->validate($validEventInput, $eventReferences);
$assert(
    $validEventResult['error'] === null
        && $validEventResult['event']['judul'] === 'Campus Meetup'
        && $validEventResult['event']['jam'] === '09:30:00'
        && $validEventResult['event']['kuota'] === 0,
    'event validation accepts schema-valid fields and normalizes time and whitespace'
);
$invalidEventInputs = [
    ['judul' => str_repeat('a', 151), 'message' => 'judul'],
    ['tanggal' => '2027-02-29', 'message' => 'tanggal'],
    ['jam' => '25:61', 'message' => 'jam'],
    ['lokasi' => str_repeat('x', 151), 'message' => 'lokasi'],
    ['kuota' => '-1', 'message' => 'kuota'],
    ['kuota' => '2147483648', 'message' => 'kuota'],
    ['status' => 'Archived', 'message' => 'status'],
];
foreach ($invalidEventInputs as $invalidInput) {
    $candidate = $validEventInput;
    $candidate[array_key_first(array_diff_key($invalidInput, ['message' => true]))]
        = $invalidInput[array_key_first(array_diff_key($invalidInput, ['message' => true]))];
    $result = $eventValidator->validate($candidate, $eventReferences);
    $assert(
        $result['error'] !== null,
        'event validation rejects invalid ' . $invalidInput['message']
    );
}
$emptyEventDescription = $validEventInput;
$emptyEventDescription['deskripsi'] = str_repeat('a', 65536);
$oversizedDescriptionResult = $eventValidator->validate($emptyEventDescription, $eventReferences);
$assert(
    str_contains((string) $oversizedDescriptionResult['error'], 'Deskripsi'),
    'event validation enforces MySQL TEXT byte capacity'
);
$missingRelationshipCases = [
    ['id_organisasi', 'organizations', 'Pilih organisasi'],
    ['id_panitia', 'committees', 'Pilih panitia'],
    ['id_kategori', 'categories', 'Pilih kategori'],
];
foreach ($missingRelationshipCases as [$field, $referenceName, $message]) {
    $candidate = $validEventInput;
    $candidate[$field] = '999';
    $result = $eventValidator->validate($candidate, $eventReferences);
    $assert(
        str_contains((string) $result['error'], $message),
        'event validation rejects unavailable relation ' . $referenceName
    );
}
$invalidUtf8Event = $validEventInput;
$invalidUtf8Event['judul'] = "\xC3";
$assert(
    $eventValidator->validate($invalidUtf8Event, $eventReferences)['error'] !== null,
    'event validation rejects malformed UTF-8 input'
);
$noEventPoster = (new \CampusHub\Models\EventPoster())->validate(null);
$assert(
    $noEventPoster['error'] === null && $noEventPoster['temporary_path'] === null,
    'event poster is optional when no file is submitted'
);
$eventUploadFailure = (new \CampusHub\Models\EventPoster())->validate([
    'name' => 'poster.png',
    'tmp_name' => '',
    'error' => UPLOAD_ERR_INI_SIZE,
]);
$assert(
    $eventUploadFailure['error'] !== null,
    'event poster validator rejects upload-size errors'
);
$eventPosterPath = tempnam(sys_get_temp_dir(), 'campushub-event-');
$spoofedEventPosterPath = tempnam(sys_get_temp_dir(), 'campushub-event-');
$oversizedEventPosterPath = tempnam(sys_get_temp_dir(), 'campushub-event-');
$eventPosterFixtures = [$eventPosterPath, $spoofedEventPosterPath, $oversizedEventPosterPath];
try {
    $image = imagecreatetruecolor(2, 2);
    if (!imagepng($image, $eventPosterPath)) {
        throw new RuntimeException('Could not create event poster fixture for tests.');
    }
    imagedestroy($image);
    copy($eventPosterPath, $spoofedEventPosterPath);
    file_put_contents($oversizedEventPosterPath, str_repeat('x', MAX_UPLOAD_SIZE + 1));

    $validPosterInspection = \CampusHub\Models\EventPoster::inspect($eventPosterPath, 'poster.png');
    $assert(
        $validPosterInspection['error'] === null
            && $validPosterInspection['extension'] === 'png',
        'event poster validation accepts a genuine PNG under the configured size limit'
    );
    $spoofedPosterInspection = \CampusHub\Models\EventPoster::inspect($spoofedEventPosterPath, 'poster.jpg');
    $assert(
        $spoofedPosterInspection['error'] !== null,
        'event poster validation rejects extension and MIME mismatches'
    );
    $invalidPosterExtension = \CampusHub\Models\EventPoster::inspect($eventPosterPath, 'poster.svg');
    $assert(
        $invalidPosterExtension['error'] !== null,
        'event poster validation rejects disallowed extensions'
    );
    $oversizedPosterInspection = \CampusHub\Models\EventPoster::inspect(
        $oversizedEventPosterPath,
        'poster.png'
    );
    $assert(
        $oversizedPosterInspection['error'] !== null,
        'event poster validation rejects files over 2 MB'
    );
} finally {
    foreach ($eventPosterFixtures as $fixture) {
        if (is_string($fixture) && is_file($fixture)) {
            unlink($fixture);
        }
    }
}
$eventXssView = \CampusHub\Core\View::render('admin/events/index', [
    'userName' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'photo' => 'default.png',
    'flash' => null,
    'events' => [[
        'id_event' => 1,
        'judul' => '<script>alert(1)</script>',
        'nama_kategori' => '<img src=x onerror=alert(1)>',
        'nama_organisasi' => 'Campus Club',
        'nama_panitia' => 'Panitia',
        'tanggal' => '2028-02-29',
        'jam' => '09:30:00',
        'lokasi' => 'Aula',
        'kuota' => 25,
        'status' => 'Draft',
        'poster' => '../poster.png',
    ], [
        'id_event' => 2,
        'judul' => 'Invalid poster path',
        'poster' => '..',
    ]],
]);
$assert(
    str_contains($eventXssView, '&lt;script&gt;alert(1)&lt;/script&gt;')
        && str_contains($eventXssView, '&lt;img src=x onerror=alert(1)&gt;')
        && !str_contains($eventXssView, '<script>alert(1)</script>')
        && str_contains($eventXssView, '/assets/uploads/poster/poster.png')
        && !str_contains($eventXssView, 'src="/assets/uploads/poster/.."'),
    'event list escapes dynamic data and constrains poster URLs to their basename'
);
$eventFormView = \CampusHub\Core\View::render('admin/events/form', [
    'userName' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'photo' => 'default.png',
    'flash' => null,
    'formAction' => '/admin/event/perbarui',
    'heading' => 'Edit Event',
    'event' => [
        'id_event' => 8,
        'id_organisasi' => 4,
        'id_panitia' => 7,
        'id_kategori' => 9,
        'judul' => '<script>alert(2)</script>',
        'deskripsi' => '<img src=x onerror=alert(3)>',
        'poster' => '../poster.png',
        'tanggal' => '2028-02-29',
        'jam' => '09:30:00',
        'lokasi' => 'Aula',
        'kuota' => 10,
        'status' => 'Draft',
    ],
    'references' => $eventReferences,
]);
$assert(
    str_contains($eventFormView, 'name="_csrf_token"')
        && str_contains($eventFormView, 'enctype="multipart/form-data"')
        && str_contains($eventFormView, '&lt;script&gt;alert(2)&lt;/script&gt;')
        && str_contains($eventFormView, '&lt;img src=x onerror=alert(3)&gt;')
        && str_contains($eventFormView, '/assets/uploads/poster/poster.png')
        && !str_contains($eventFormView, '<script>alert(2)</script>'),
    'event form includes CSRF, schema fields, safe poster URL, and escaped values'
);

$eventRouter = $makeRouter();
$registerAuthRoutes($eventRouter);
$eventAdminSession = [
    'is_login' => true,
    'user_id' => 1,
    'nama' => 'Admin Test',
    'role' => ROLE_ADMIN,
    'foto' => 'default.png',
    '_mvc_csrf_token' => $categoryCsrf,
];
foreach ([
    '/admin/event',
    '/admin/event/index.php',
    '/admin/event/tambah',
    '/admin/event/edit?id=1',
] as $eventPath) {
    $eventGuest = $eventRouter->dispatch(new Request('GET', $eventPath));
    $assert(
        $eventGuest->status() === 302
            && ($eventGuest->headers()['Location'] ?? '') === '/login',
        'event route redirects guests to login: ' . $eventPath
    );
}
$eventWrongRole = $eventRouter->dispatch(new Request(
    'GET',
    '/admin/event',
    [],
    [],
    ['is_login' => true, 'user_id' => 2, 'role' => ROLE_COMMITTEE]
));
$assert(
    $eventWrongRole->status() === 302
        && ($eventWrongRole->headers()['Location'] ?? '') === '/committee/dashboard',
    'event routes redirect non-admin roles to their role dashboard'
);
$eventUnknownRole = $eventRouter->dispatch(new Request(
    'GET',
    '/admin/event',
    [],
    [],
    ['is_login' => true, 'user_id' => 3, 'role' => 'Unknown']
));
$assert($eventUnknownRole->status() === 403, 'event routes deny unknown roles');
$invalidEventEdit = $eventRouter->dispatch(new Request(
    'GET',
    '/admin/event/edit?id=invalid',
    [],
    [],
    $eventAdminSession
));
$assert($invalidEventEdit->status() === 404, 'event edit rejects an invalid identifier');
$invalidEventUpdate = $eventRouter->dispatch(new Request(
    'POST',
    '/admin/event/perbarui',
    [],
    ['_csrf_token' => $categoryCsrf, 'id' => 'invalid'],
    $eventAdminSession
));
$assert($invalidEventUpdate->status() === 404, 'event update rejects an invalid identifier');
$eventMissingCsrf = $eventRouter->dispatch(new Request(
    'POST',
    '/admin/event/simpan',
    [],
    ['judul' => 'Event'],
    $eventAdminSession
));
$assert($eventMissingCsrf->status() === 403, 'event creation rejects missing CSRF token');
$eventInvalidCsrf = $eventRouter->dispatch(new Request(
    'POST',
    '/admin/event/perbarui',
    [],
    ['_csrf_token' => str_repeat('0', 64), 'id' => '1'],
    $eventAdminSession
));
$assert($eventInvalidCsrf->status() === 403, 'event update rejects invalid CSRF token');
$eventWrongMethod = $eventRouter->dispatch(new Request(
    'GET',
    '/admin/event/simpan',
    [],
    [],
    $eventAdminSession
));
$assert(
    $eventWrongMethod->status() === 405
        && ($eventWrongMethod->headers()['Allow'] ?? '') === 'POST',
    'event creation endpoint only accepts POST'
);
$eventDeleteRoute = $eventRouter->dispatch(new Request(
    'POST',
    '/admin/event/hapus',
    [],
    ['_csrf_token' => $categoryCsrf],
    $eventAdminSession
));
$assert($eventDeleteRoute->status() === 404, 'event deletion rejects requests without a valid event ID');

$dashboardRouter = $makeRouter();
$registerAuthRoutes($dashboardRouter);
$dashboardSessions = [
    ROLE_ADMIN => ['is_login' => true, 'user_id' => 1, 'role' => ROLE_ADMIN, 'nama' => 'Admin Test'],
    ROLE_COMMITTEE => ['is_login' => true, 'user_id' => 2, 'role' => ROLE_COMMITTEE, 'nama' => 'Panitia Test'],
    ROLE_STUDENT => ['is_login' => true, 'user_id' => 3, 'role' => ROLE_STUDENT, 'nama' => 'Mahasiswa Test'],
];
$dashboardPaths = [
    ROLE_ADMIN => ['/admin/dashboard', '/admin/dashboard.php'],
    ROLE_COMMITTEE => ['/committee/dashboard', '/committee/dashboard.php'],
    ROLE_STUDENT => ['/student/dashboard', '/student/dashboard.php'],
];
foreach ($dashboardSessions as $role => $session) {
    foreach ($dashboardPaths[$role] as $dashboardPath) {
        $dashboardResponse = $dashboardRouter->dispatch(new Request('GET', $dashboardPath, [], [], $session));
        $assert(
            $dashboardResponse->status() === 200
                && str_contains($dashboardResponse->body(), $session['nama']),
            $role . ' dashboard route renders its session identity: ' . $dashboardPath
        );
    }
}

$committeeDashboard = $dashboardRouter->dispatch(new Request(
    'GET',
    '/committee/dashboard',
    [],
    [],
    $dashboardSessions[ROLE_COMMITTEE]
));
$assert(
    $committeeDashboard->status() === 200
        && str_contains($committeeDashboard->body(), 'committee-overview-title')
        && str_contains($committeeDashboard->body(), 'Event yang ditugaskan')
        && str_contains($committeeDashboard->body(), 'Dokumentasi terbaru')
        && str_contains($committeeDashboard->body(), 'Progres check-in')
        && str_contains($committeeDashboard->body(), 'Peserta hari ini')
        && str_contains($committeeDashboard->body(), 'Ringkasan dan progres di atas dihitung')
        && str_contains($committeeDashboard->body(), '/public/assets/css/dashboard-portals.css'),
    'committee dashboard renders database-backed operational summary panels'
);

foreach ($dashboardPaths as $role => $paths) {
    $guestDashboard = $dashboardRouter->dispatch(new Request('GET', $paths[0]));
    $assert(
        $guestDashboard->status() === 302
            && ($guestDashboard->headers()['Location'] ?? '') === '/login',
        $role . ' dashboard redirects guest to login'
    );
}

$wrongRoleDashboard = $dashboardRouter->dispatch(new Request(
    'GET',
    '/admin/dashboard',
    [],
    [],
    $dashboardSessions[ROLE_STUDENT]
));
$assert(
    $wrongRoleDashboard->status() === 302
        && ($wrongRoleDashboard->headers()['Location'] ?? '') === '/student/dashboard',
    'dashboard route redirects wrong role to its own dashboard'
);
$dashboardDestinations = [
    ROLE_ADMIN => '/admin/dashboard',
    ROLE_COMMITTEE => '/committee/dashboard',
    ROLE_STUDENT => '/student/dashboard',
];
foreach ($dashboardPaths as $targetRole => $paths) {
    foreach ($dashboardSessions as $currentRole => $session) {
        if ($currentRole === $targetRole) {
            continue;
        }

        $wrongRole = $dashboardRouter->dispatch(new Request('GET', $paths[0], [], [], $session));
        $assert(
            $wrongRole->status() === 302
                && ($wrongRole->headers()['Location'] ?? '') === $dashboardDestinations[$currentRole],
            $currentRole . ' is redirected away from the ' . $targetRole . ' dashboard'
        );
    }
}
$invalidRoleDashboard = $dashboardRouter->dispatch(new Request(
    'GET',
    '/admin/dashboard',
    [],
    [],
    ['is_login' => true, 'user_id' => 4, 'role' => 'Unknown']
));
$assert($invalidRoleDashboard->status() === 403, 'dashboard route rejects an unknown role');
foreach ([ROLE_COMMITTEE => '/committee/dashboard', ROLE_STUDENT => '/student/dashboard'] as $path) {
    $invalidRoleResponse = $dashboardRouter->dispatch(new Request(
        'GET',
        $path,
        [],
        [],
        ['is_login' => true, 'user_id' => 5, 'role' => 'Unknown']
    ));
    $assert($invalidRoleResponse->status() === 403, 'dashboard route rejects unknown role at ' . $path);
}

$adminDashboardHtml = $dashboardRouter->dispatch(new Request(
    'GET',
    '/admin/dashboard',
    [],
    [],
    $dashboardSessions[ROLE_ADMIN] + ['foto' => 'default.png']
))->body();
$assert(
    str_contains($adminDashboardHtml, '/public/assets/css/dashboard-portals.css')
        && str_contains($adminDashboardHtml, '/public/assets/images/logo.png'),
    'admin dashboard renders the role dashboard stylesheet and CampusHub logo'
);
$assert(
    str_contains($adminDashboardHtml, 'href="/admin/event/"')
        && str_contains($adminDashboardHtml, 'href="/admin/kategori/"')
        && str_contains($adminDashboardHtml, 'href="/admin/organisasi/"')
        && str_contains($adminDashboardHtml, 'href="/logout.php"'),
    'admin dashboard links only to available administration modules and logout'
);
$assert(
    str_contains($adminDashboardHtml, 'ch-admin-stats')
        && str_contains($adminDashboardHtml, 'href="/admin/users"')
        && str_contains($adminDashboardHtml, 'href="/admin/reports/export"'),
    'admin dashboard renders database-backed analytics and operational shortcuts'
);

foreach ([
    ROLE_STUDENT => [
        '/student/dashboard',
        'Cari Event Kampus Favoritmu',
    ],
    ROLE_COMMITTEE => [
        '/committee/dashboard',
        'Ringkasan dan progres di atas dihitung',
    ],
] as $dashboardRole => [$dashboardPath, $honestState]) {
    $roleDashboardHtml = $dashboardRouter->dispatch(new Request(
        'GET',
        $dashboardPath,
        [],
        [],
        $dashboardSessions[$dashboardRole]
    ))->body();
    if ($dashboardRole === ROLE_STUDENT) {
        $assert(
            str_contains($roleDashboardHtml, '/public/assets/css/dashboard-portals.css')
                && str_contains($roleDashboardHtml, 'class="sp-header')
                && str_contains($roleDashboardHtml, $honestState),
            'Mahasiswa dashboard renders the current student portal and event search'
        );
    } else {
        $assert(
            str_contains($roleDashboardHtml, '/public/assets/css/dashboard-portals.css')
                && str_contains($roleDashboardHtml, '/public/assets/images/logo.png')
                && str_contains($roleDashboardHtml, $honestState)
                && str_contains($roleDashboardHtml, 'href="/logout.php"'),
            'Panitia dashboard renders its role UI, honest status, and logout'
        );
    }
}

$escapedDashboardName = '<script>alert("xss")</script>';
foreach ($dashboardPaths as $role => $paths) {
    $escapedDashboardHtml = $dashboardRouter->dispatch(new Request(
        'GET',
        $paths[0],
        [],
        [],
        array_replace($dashboardSessions[$role], [
            'foto' => 'default.png',
            'nama' => $escapedDashboardName,
        ])
    ))->body();
    $assert(
        str_contains($escapedDashboardHtml, '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;')
            && !str_contains($escapedDashboardHtml, $escapedDashboardName),
        $role . ' dashboard escapes its session-provided display name'
    );
}

$dashboardRoleRoutes = [
    ROLE_ADMIN => '/admin/dashboard',
    ROLE_STUDENT => '/student/dashboard',
    ROLE_COMMITTEE => '/committee/dashboard',
];
foreach ($dashboardRoleRoutes as $role => $path) {
    $roleDashboardHtml = $dashboardRouter->dispatch(new Request(
        'GET',
        $path,
        [],
        [],
        $dashboardSessions[$role]
    ))->body();
    $assert(
        substr_count($roleDashboardHtml, 'class="ch-dashboard ') === 1
            && str_contains($roleDashboardHtml, 'id="main-content"')
            && str_contains($roleDashboardHtml, 'Lewati ke konten utama'),
        $role . ' dashboard provides a scoped role theme and skip navigation'
    );
}

$assert(
    !str_contains($adminDashboardHtml, 'href="#"'),
    'admin dashboard contains no placeholder navigation links'
);
$adminSidebar = file_get_contents(dirname(__DIR__) . '/includes/admin/sidebar.php');
$assert(
    is_string($adminSidebar)
        && str_contains($adminSidebar, 'href="/admin/event/"')
        && str_contains($adminSidebar, 'href="/admin/kategori/"')
        && str_contains($adminSidebar, 'href="/admin/organisasi/"')
        && !preg_match('~href="/admin/(?:users|peserta|dokumentasi|sertifikat|laporan)/~', $adminSidebar)
        && !str_contains($adminSidebar, 'href="#"'),
    'shared Admin navigation contains only implemented modules'
);
$projectRoot = dirname(__DIR__);
$assert(
    is_file($projectRoot . '/public/assets/css/style.css')
        && is_file($projectRoot . '/public/assets/js/login.js')
        && is_file($projectRoot . '/public/assets/images/logo.png')
        && is_file($projectRoot . '/public/index.php')
        && !is_file($projectRoot . '/admin/dashboard.php')
        && is_file($projectRoot . '/admin/event/index.php')
        && is_file($projectRoot . '/admin/kategori/index.php')
        && is_file($projectRoot . '/admin/organisasi/index.php')
        && !is_dir($projectRoot . '/student')
        && !is_dir($projectRoot . '/committee')
        && !is_dir($projectRoot . '/configs')
        && !is_dir($projectRoot . '/assets/css')
        && !is_dir($projectRoot . '/assets/js')
        && !is_dir($projectRoot . '/assets/img')
        && is_dir($projectRoot . '/assets/uploads'),
    'public entry point and assets are canonical while role adapters and uploads retain intended locations'
);
$publicEntrypoint = file_get_contents($projectRoot . '/public/index.php');
$rootEntrypoint = file_get_contents($projectRoot . '/index.php');
$assert(
    is_string($publicEntrypoint)
        && str_contains($publicEntrypoint, '/app/bootstrap.php')
        && str_contains($publicEntrypoint, 'Request::fromGlobals()')
        && str_contains($publicEntrypoint, '$router->dispatch($request)')
        && is_string($rootEntrypoint)
        && str_contains($rootEntrypoint, '/public/index.php')
        && !str_contains($rootEntrypoint, '/app/bootstrap.php'),
    'public/index.php is the MVC front controller and root index is only a compatibility entry point'
);
$legacyAdminAdapters = [
    'admin/event/index.php',
    'admin/kategori/index.php',
    'admin/organisasi/index.php',
];
$adaptersUsePublicEntryPoint = true;
foreach ($legacyAdminAdapters as $legacyAdminAdapter) {
    $adapter = file_get_contents($projectRoot . '/' . $legacyAdminAdapter);
    if (!is_string($adapter) || !str_contains($adapter, '/public/index.php')) {
        $adaptersUsePublicEntryPoint = false;
        break;
    }
}
$assert(
    $adaptersUsePublicEntryPoint,
    'remaining Admin adapters forward directly to the MVC public entry point'
);
$adminLayoutViews = [
    'app/Views/admin/categories/index.php',
    'app/Views/admin/categories/form.php',
    'app/Views/admin/events/index.php',
    'app/Views/admin/events/form.php',
    'app/Views/admin/organizations/index.php',
    'app/Views/admin/organizations/form.php',
];
$usesSharedAdminLayout = true;
foreach ($adminLayoutViews as $adminLayoutView) {
    $template = file_get_contents($projectRoot . '/' . $adminLayoutView);
    if (!is_string($template)
        || !str_contains($template, '/includes/admin/layout-start.php')
        || !str_contains($template, '/includes/admin/layout-end.php')) {
        $usesSharedAdminLayout = false;
        break;
    }
}
$assert($usesSharedAdminLayout, 'Admin CRUD views use one shared shell layout');
$adminDashboardTemplate = file_get_contents($projectRoot . '/app/Views/dashboard/admin.php');
$adminLayoutStart = file_get_contents($projectRoot . '/includes/admin/layout-start.php');
$adminDashboardUsesSharedChrome = is_string($adminDashboardTemplate)
    && str_contains($adminDashboardTemplate, '/includes/admin/sidebar.php')
    && str_contains($adminDashboardTemplate, '/includes/admin/topbar.php')
    && str_contains($adminDashboardTemplate, '/includes/admin/footer.php');
$adminCrudUsesDashboardTheme = is_string($adminLayoutStart)
    && str_contains($adminLayoutStart, 'ch-dashboard ch-dashboard--admin')
    && str_contains($adminLayoutStart, 'dashboard-portals.css');
foreach ($adminLayoutViews as $adminLayoutView) {
    $template = file_get_contents($projectRoot . '/' . $adminLayoutView);
    if (!is_string($template) || !str_contains($template, 'class="ch-content ch-admin-page"')) {
        $adminCrudUsesDashboardTheme = false;
        break;
    }
}
$assert(
    $adminDashboardUsesSharedChrome && $adminCrudUsesDashboardTheme,
    'Admin dashboard and CRUD screens share navigation, header, footer, and dashboard theme'
);

$logoutConfirmation = $authRouter->dispatch(new Request(
    'GET',
    '/logout.php',
    [],
    [],
    ['is_login' => true, 'user_id' => 41, 'role' => ROLE_STUDENT]
));
$assert(
    $logoutConfirmation->status() === 200
        && str_contains($logoutConfirmation->body(), 'name="_csrf_token"')
        && str_contains($logoutConfirmation->body(), 'action="/logout"'),
    'legacy logout URL displays a CSRF-protected POST confirmation'
);

$authPage = $authRouter->dispatch(new Request('GET', '/login'));
$assert(
    $authPage->status() === 200
        && str_contains($authPage->body(), 'name="_csrf_token"')
        && str_contains($authPage->body(), '/public/assets/css/login.css'),
    'MVC login page renders CSRF field and existing stylesheet'
);
$legacyLoginPage = $authRouter->dispatch(new Request('GET', '/login.php'));
$assert($legacyLoginPage->status() === 200, 'legacy login URL remains routed without a PHP adapter');
$legacyRegisterPage = $authRouter->dispatch(new Request('GET', '/register.php'));
$assert($legacyRegisterPage->status() === 200, 'legacy registration URL remains routed without a PHP adapter');

$invalidLoginCsrf = $authRouter->dispatch(new Request('POST', '/login'));
$assert($invalidLoginCsrf->status() === 403, 'MVC login rejects a missing CSRF token');
$legacyLoginCsrf = $authRouter->dispatch(new Request('POST', '/login_process.php'));
$assert($legacyLoginCsrf->status() === 403, 'legacy login process URL retains CSRF protection');
$legacyRegisterCsrf = $authRouter->dispatch(new Request('POST', '/register_process.php'));
$assert($legacyRegisterCsrf->status() === 403, 'legacy registration process URL retains CSRF protection');

$testToken = \CampusHub\Core\CsrfToken::get();
$invalidRegistration = $authRouter->dispatch(new Request(
    'POST',
    '/register',
    [],
    ['_csrf_token' => $testToken],
    ['_mvc_csrf_token' => $testToken]
));
$assert(
    $invalidRegistration->status() === 302
        && ($invalidRegistration->headers()['Location'] ?? '') === '/register'
        && isset($_SESSION['flash']),
    'MVC registration validates input before database access'
);

$sessionIdBeforeLogin = session_id();
\CampusHub\Core\Session::authenticate([
    'id_user' => 41,
    'id_organisasi' => null,
    'nama' => 'Test User',
    'username' => 'test-user',
    'email' => 'test@example.invalid',
    'role' => ROLE_STUDENT,
    'foto' => 'default.png',
]);
$assert(
    session_id() !== $sessionIdBeforeLogin
        && ($_SESSION['is_login'] ?? false) === true
        && ($_SESSION['user_id'] ?? null) === 41
        && !isset($_SESSION['_mvc_csrf_token']),
    'session authentication rotates ID and preserves legacy identity keys'
);

$sessionIdBeforeLogout = session_id();
$logoutToken = \CampusHub\Core\CsrfToken::get();
$logoutResponse = $authRouter->dispatch(new Request(
    'POST',
    '/logout',
    [],
    ['_csrf_token' => $logoutToken],
    $_SESSION
));
$assert(
    $logoutResponse->status() === 302
        && ($logoutResponse->headers()['Location'] ?? '') === '/login'
        && session_id() !== $sessionIdBeforeLogout
        && !isset($_SESSION['is_login'])
        && !isset($_SESSION['user_id']),
    'POST logout validates CSRF and invalidates the authenticated session'
);

$expectedTests = 141;
echo sprintf("\n%d/%d tests passed.\n", $passed, $expectedTests);
$exitCode = $failed === 0 && $passed === $expectedTests ? 0 : 1;
ob_end_flush();
exit($exitCode);
