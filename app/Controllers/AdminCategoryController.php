<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\CategoryModel;
use mysqli_sql_exception;
use RuntimeException;

final class AdminCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $categories = (new CategoryModel())->all();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Layanan kategori sedang tidak tersedia.', 503);
        }

        return $this->view('admin/categories/index', $this->layoutData($request) + [
            'categories' => $categories,
            'flash' => Flash::pull(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->view('admin/categories/form', $this->layoutData($request) + [
            'category' => null,
            'formAction' => '/admin/kategori/simpan',
            'flash' => Flash::pull(),
            'heading' => 'Tambah Kategori',
        ]);
    }

    public function edit(Request $request): Response
    {
        $id = $this->categoryId($request->input('id'));

        if ($id === null) {
            return Response::text('Kategori tidak ditemukan.', 404);
        }

        try {
            $category = (new CategoryModel())->findById($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Layanan kategori sedang tidak tersedia.', 503);
        }

        if ($category === null) {
            return Response::text('Kategori tidak ditemukan.', 404);
        }

        return $this->view('admin/categories/form', $this->layoutData($request) + [
            'category' => $category,
            'formAction' => '/admin/kategori/perbarui',
            'flash' => Flash::pull(),
            'heading' => 'Edit Kategori',
        ]);
    }

    public function store(Request $request): Response
    {
        [$name, $description] = $this->validatedInput($request);

        if ($name === null) {
            return Response::redirect('/admin/kategori/tambah');
        }

        try {
            (new CategoryModel())->create($name, $description);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Kategori tidak dapat disimpan. Silakan coba lagi.');

            return Response::redirect('/admin/kategori/tambah');
        }

        Flash::set('Success', 'Kategori berhasil ditambahkan.');

        return Response::redirect('/admin/kategori/');
    }

    public function update(Request $request): Response
    {
        $id = $this->categoryId($request->post('id'));

        if ($id === null) {
            return Response::text('Kategori tidak ditemukan.', 404);
        }

        [$name, $description] = $this->validatedInput($request);

        if ($name === null) {
            return Response::redirect('/admin/kategori/edit?id=' . $id);
        }

        try {
            $model = new CategoryModel();
            $category = $model->findById($id);

            if ($category === null) {
                return Response::text('Kategori tidak ditemukan.', 404);
            }

            $model->update($id, $name, $description);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Kategori tidak dapat diperbarui. Silakan coba lagi.');

            return Response::redirect('/admin/kategori/edit?id=' . $id);
        }

        Flash::set('Success', 'Kategori berhasil diperbarui.');

        return Response::redirect('/admin/kategori/');
    }

    public function delete(Request $request): Response
    {
        $id = $this->categoryId($request->post('id'));
        if ($id === null) {
            return Response::text('Kategori tidak ditemukan.', 404);
        }

        try {
            $result = (new CategoryModel())->deleteIfUnused($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Kategori tidak dapat dihapus. Silakan coba lagi.');
            return Response::redirect('/admin/kategori/');
        }

        if ($result === 'not_found') {
            return Response::text('Kategori tidak ditemukan.', 404);
        }

        if ($result === 'in_use') {
            Flash::set('Warning', 'Kategori masih digunakan oleh event dan tidak dapat dihapus.');
            return Response::redirect('/admin/kategori/');
        }

        Flash::set('Success', 'Kategori berhasil dihapus.');
        return Response::redirect('/admin/kategori/');
    }

    private function layoutData(Request $request): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => (string) $request->session('role', ''),
            'photo' => (string) $request->session('foto', basename(DEFAULT_PROFILE)),
            'activeAdminSection' => 'kategori',
        ];
    }

    private function categoryId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function validatedInput(Request $request): array
    {
        $name = $request->post('nama_kategori');
        $name = is_string($name) ? trim($name) : '';
        $characterCount = preg_match_all('/./us', $name);
        $description = $request->post('deskripsi');
        $description = is_string($description) ? trim($description) : '';
        $descriptionCount = preg_match_all('/./us', $description);

        if (
            $name === ''
            || $characterCount === false
            || $characterCount > 100
            || $descriptionCount === false
            || $descriptionCount > 2000
        ) {
            Flash::set('Danger', 'Nama kategori wajib diisi dan maksimal 100 karakter. Deskripsi maksimal 2.000 karakter.');

            return [null, ''];
        }

        return [$name, $description];
    }

    private function logDatabaseFailure(mysqli_sql_exception|RuntimeException $exception): void
    {
        error_log('CampusHub category request failed (' . get_class($exception) . ').');
    }
}
