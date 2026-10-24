<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\OrganizationLogo;
use CampusHub\Models\OrganizationModel;
use mysqli_sql_exception;
use RuntimeException;

final class AdminOrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $organizations = (new OrganizationModel())->all();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Layanan organisasi sedang tidak tersedia.', 503);
        }

        return $this->view('admin/organizations/index', $this->layoutData($request) + [
            'organizations' => $organizations,
            'flash' => Flash::pull(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/organizations/form', $this->layoutData($request) + [
            'flash' => Flash::pull(),
            'formAction' => '/admin/organisasi/simpan',
            'heading' => 'Tambah Organisasi',
            'organization' => null,
        ]);
    }

    public function edit(Request $request): Response
    {
        $id = $this->organizationId($request->input('id'));

        if ($id === null) {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        try {
            $organization = (new OrganizationModel())->findById($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Layanan organisasi sedang tidak tersedia.', 503);
        }

        if ($organization === null) {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        return $this->view('admin/organizations/form', $this->layoutData($request) + [
            'flash' => Flash::pull(),
            'formAction' => '/admin/organisasi/perbarui',
            'heading' => 'Edit Organisasi',
            'organization' => $organization,
        ]);
    }

    public function store(Request $request): Response
    {
        $validated = $this->validatedInput($request);

        if ($validated['error'] !== null) {
            Flash::set('Danger', $validated['error']);
            return Response::redirect('/admin/organisasi/tambah');
        }

        $upload = $validated['upload'];
        $logoFilename = null;

        if ($upload['temporary_path'] !== null) {
            try {
                $logoFilename = (new OrganizationLogo())->store(
                    $upload['temporary_path'],
                    $upload['extension']
                );
            } catch (RuntimeException $exception) {
                $this->logUploadFailure($exception);
                Flash::set('Danger', 'Logo organisasi gagal disimpan. Silakan coba lagi.');
                return Response::redirect('/admin/organisasi/tambah');
            }
        }

        try {
            (new OrganizationModel())->create(
                $validated['name'],
                $validated['description'],
                $logoFilename
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($logoFilename !== null) {
                (new OrganizationLogo())->removeStored($logoFilename);
            }

            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Organisasi tidak dapat disimpan. Silakan coba lagi.');
            return Response::redirect('/admin/organisasi/tambah');
        }

        Flash::set('Success', 'Organisasi berhasil ditambahkan.');

        return Response::redirect('/admin/organisasi/');
    }

    public function update(Request $request): Response
    {
        $id = $this->organizationId($request->post('id'));

        if ($id === null) {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        $validated = $this->validatedInput($request);

        if ($validated['error'] !== null) {
            Flash::set('Danger', $validated['error']);
            return Response::redirect('/admin/organisasi/edit?id=' . $id);
        }

        try {
            $model = new OrganizationModel();
            $organization = $model->findById($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Organisasi tidak dapat diperbarui. Silakan coba lagi.');
            return Response::redirect('/admin/organisasi/edit?id=' . $id);
        }

        if ($organization === null) {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        $upload = $validated['upload'];
        $newLogoFilename = null;

        if ($upload['temporary_path'] !== null) {
            try {
                $newLogoFilename = (new OrganizationLogo())->store(
                    $upload['temporary_path'],
                    $upload['extension']
                );
            } catch (RuntimeException $exception) {
                $this->logUploadFailure($exception);
                Flash::set('Danger', 'Logo organisasi gagal disimpan. Logo lama tetap digunakan.');
                return Response::redirect('/admin/organisasi/edit?id=' . $id);
            }
        }

        try {
            $model->update(
                $id,
                $validated['name'],
                $validated['description'],
                $newLogoFilename
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($newLogoFilename !== null) {
                (new OrganizationLogo())->removeStored($newLogoFilename);
            }

            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Organisasi tidak dapat diperbarui. Silakan coba lagi.');
            return Response::redirect('/admin/organisasi/edit?id=' . $id);
        }

        Flash::set('Success', 'Organisasi berhasil diperbarui.');

        return Response::redirect('/admin/organisasi/');
    }

    public function delete(Request $request): Response
    {
        $id = $this->organizationId($request->post('id'));
        if ($id === null) {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        try {
            $model = new OrganizationModel();
            $organization = $model->findById($id);
            if ($organization === null) {
                return Response::text('Organisasi tidak ditemukan.', 404);
            }
            $result = $model->deleteIfUnused($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Organisasi tidak dapat dihapus. Silakan coba lagi.');
            return Response::redirect('/admin/organisasi/');
        }

        if ($result === 'in_use') {
            Flash::set('Warning', 'Organisasi masih memiliki event atau anggota dan tidak dapat dihapus.');
            return Response::redirect('/admin/organisasi/');
        }

        if ($result !== 'deleted') {
            return Response::text('Organisasi tidak ditemukan.', 404);
        }

        $logo = is_string($organization['logo'] ?? null) ? basename($organization['logo']) : '';
        if ($logo !== '') {
            (new OrganizationLogo())->removeStored($logo);
        }
        Flash::set('Success', 'Organisasi berhasil dihapus.');
        return Response::redirect('/admin/organisasi/');
    }

    private function layoutData(Request $request): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => (string) $request->session('role', ''),
            'photo' => (string) $request->session('foto', basename(DEFAULT_PROFILE)),
            'activeAdminSection' => 'organisasi',
        ];
    }

    private function organizationId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function validatedInput(Request $request): array
    {
        $name = $request->post('nama_organisasi');
        $name = is_string($name) ? trim($name) : '';
        $nameLength = preg_match_all('/./us', $name);
        $description = $request->post('deskripsi');
        $errors = [];

        if ($name === '' || $nameLength === false || $nameLength > 100) {
            $errors[] = 'Nama organisasi wajib diisi dan maksimal 100 karakter.';
        }

        if (!is_string($description) || preg_match('//u', $description) !== 1) {
            $errors[] = 'Deskripsi organisasi tidak valid.';
            $description = '';
        } elseif (strlen($description) > 65535) {
            $errors[] = 'Deskripsi organisasi melebihi kapasitas kolom database.';
        }

        $upload = (new OrganizationLogo())->validate($request->file('logo'));

        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        }

        return [
            'error' => $errors === [] ? null : implode("\n", $errors),
            'name' => $name,
            'description' => $description,
            'upload' => $upload,
        ];
    }

    private function logDatabaseFailure(mysqli_sql_exception | RuntimeException $exception): void
    {
        error_log('CampusHub organization storage failed (' . get_class($exception) . ').');
    }

    private function logUploadFailure(RuntimeException $exception): void
    {
        error_log('CampusHub organization logo upload failed (' . get_class($exception) . ').');
    }
}
