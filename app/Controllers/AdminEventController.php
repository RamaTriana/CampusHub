<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\EventModel;
use CampusHub\Models\EventPoster;
use CampusHub\Validation\EventInputValidator;
use mysqli_sql_exception;
use RuntimeException;

final class AdminEventController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $this->queryString($request, 'q'),
            'status' => $this->queryString($request, 'status'),
            'category' => $this->queryString($request, 'category'),
            'organization' => $this->queryString($request, 'organization'),
            'from' => $this->queryString($request, 'from'),
            'to' => $this->queryString($request, 'to'),
            'sort' => $this->queryString($request, 'sort'),
        ];
        $page = $this->pageNumber($request->input('page'));

        try {
            $model = new EventModel();
            $result = $model->adminList($filters, 20, ($page - 1) * 20);
            $references = $model->referenceOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Layanan event sedang tidak tersedia.', 503);
        }

        return $this->view('admin/events/index', $this->layoutData($request) + [
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 20)),
            'filters' => $filters,
            'references' => $references,
            'flash' => Flash::pull(),
        ]);
    }

    public function create(Request $request): Response
    {
        try {
            $references = (new EventModel())->referenceOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Form event sedang tidak tersedia.', 503);
        }

        return $this->form($request, null, $references, 'Tambah Event', '/admin/event/simpan');
    }

    public function detail(Request $request): Response
    {
        $id = $this->eventId($request->input('id'));
        if ($id === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        try {
            $event = (new EventModel())->adminDetail($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Detail event sedang tidak tersedia.', 503);
        }

        if ($event === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        return $this->view('admin/events/detail', $this->layoutData($request) + [
            'event' => $event,
        ]);
    }

    public function edit(Request $request): Response
    {
        $id = $this->eventId($request->input('id'));

        if ($id === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        try {
            $model = new EventModel();
            $event = $model->findById($id);
            $references = $model->referenceOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Form event sedang tidak tersedia.', 503);
        }

        if ($event === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        return $this->form(
            $request,
            $event,
            $references,
            'Edit Event',
            '/admin/event/perbarui'
        );
    }

    public function store(Request $request): Response
    {
        try {
            $model = new EventModel();
            $references = $model->referenceOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Event tidak dapat disimpan. Silakan coba lagi.');

            return Response::redirect('/admin/event/tambah');
        }

        $validated = (new EventInputValidator())->validate($this->postData($request), $references);

        if ($validated['error'] !== null) {
            Flash::set('Danger', $validated['error']);

            return Response::redirect('/admin/event/tambah');
        }

        $upload = (new EventPoster())->validate($request->file('poster'));

        if ($upload['error'] !== null) {
            Flash::set('Danger', $upload['error']);

            return Response::redirect('/admin/event/tambah');
        }

        $posterFilename = null;

        if ($upload['temporary_path'] !== null) {
            try {
                $posterFilename = (new EventPoster())->store(
                    $upload['temporary_path'],
                    $upload['extension']
                );
            } catch (RuntimeException $exception) {
                $this->logUploadFailure($exception);
                Flash::set('Danger', 'Poster event gagal disimpan. Silakan coba lagi.');

                return Response::redirect('/admin/event/tambah');
            }
        }

        $event = $validated['event'];
        $event['poster'] = $posterFilename;

        try {
            $model->create($event);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($posterFilename !== null) {
                (new EventPoster())->removeStaged($posterFilename);
            }

            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Event tidak dapat disimpan. Silakan coba lagi.');

            return Response::redirect('/admin/event/tambah');
        }

        Flash::set('Success', 'Event berhasil ditambahkan.');

        return Response::redirect('/admin/event/');
    }

    public function update(Request $request): Response
    {
        $id = $this->eventId($request->post('id'));

        if ($id === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        try {
            $model = new EventModel();
            $existingEvent = $model->findById($id);
            $references = $model->referenceOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Event tidak dapat diperbarui. Silakan coba lagi.');

            return Response::redirect('/admin/event/edit?id=' . $id);
        }

        if ($existingEvent === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        $validated = (new EventInputValidator())->validate($this->postData($request), $references);

        if ($validated['error'] !== null) {
            Flash::set('Danger', $validated['error']);

            return Response::redirect('/admin/event/edit?id=' . $id);
        }

        $upload = (new EventPoster())->validate($request->file('poster'));

        if ($upload['error'] !== null) {
            Flash::set('Danger', $upload['error']);

            return Response::redirect('/admin/event/edit?id=' . $id);
        }

        $posterFilename = null;

        if ($upload['temporary_path'] !== null) {
            try {
                $posterFilename = (new EventPoster())->store(
                    $upload['temporary_path'],
                    $upload['extension']
                );
            } catch (RuntimeException $exception) {
                $this->logUploadFailure($exception);
                Flash::set('Danger', 'Poster event gagal disimpan. Poster lama tetap digunakan.');

                return Response::redirect('/admin/event/edit?id=' . $id);
            }
        }

        $event = $validated['event'];
        $event['poster'] = $posterFilename;

        try {
            if (!$model->update($id, $event)) {
                if ($posterFilename !== null) {
                    (new EventPoster())->removeStaged($posterFilename);
                }

                return Response::text('Event tidak ditemukan.', 404);
            }
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($posterFilename !== null) {
                (new EventPoster())->removeStaged($posterFilename);
            }

            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Event tidak dapat diperbarui. Silakan coba lagi.');

            return Response::redirect('/admin/event/edit?id=' . $id);
        }

        Flash::set('Success', 'Event berhasil diperbarui.');

        return Response::redirect('/admin/event/');
    }

    public function delete(Request $request): Response
    {
        $id = $this->eventId($request->post('id'));
        if ($id === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        try {
            $result = (new EventModel())->deleteIfUnused($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Event tidak dapat dihapus. Silakan coba lagi.');
            return Response::redirect('/admin/event/');
        }

        if ($result['status'] === 'in_use') {
            Flash::set('Warning', 'Event memiliki pendaftar atau dokumentasi dan tidak dapat dihapus.');
            return Response::redirect('/admin/event/');
        }
        if ($result['status'] !== 'deleted') {
            return Response::text('Event tidak ditemukan.', 404);
        }
        if (is_string($result['poster']) && $result['poster'] !== '') {
            (new EventPoster())->removeStaged($result['poster']);
        }
        Flash::set('Success', 'Event berhasil dihapus.');
        return Response::redirect('/admin/event/');
    }

    private function form(
        Request $request,
        ?array $event,
        array $references,
        string $heading,
        string $formAction
    ): Response {
        return $this->view('admin/events/form', $this->layoutData($request) + [
            'event' => $event,
            'references' => $references,
            'flash' => Flash::pull(),
            'formAction' => $formAction,
            'heading' => $heading,
        ]);
    }

    private function postData(Request $request): array
    {
        return [
            'id_organisasi' => $request->post('id_organisasi'),
            'id_panitia' => $request->post('id_panitia'),
            'id_kategori' => $request->post('id_kategori'),
            'judul' => $request->post('judul'),
            'deskripsi' => $request->post('deskripsi'),
            'tanggal' => $request->post('tanggal'),
            'jam' => $request->post('jam'),
            'lokasi' => $request->post('lokasi'),
            'kuota' => $request->post('kuota'),
            'status' => $request->post('status'),
        ];
    }

    private function layoutData(Request $request): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => (string) $request->session('role', ''),
            'photo' => (string) $request->session('foto', basename(DEFAULT_PROFILE)),
            'activeAdminSection' => 'event',
        ];
    }

    private function eventId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function queryString(Request $request, string $key): string
    {
        $value = $request->input($key, '');
        return is_string($value) ? trim($value) : '';
    }

    private function pageNumber(mixed $value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($page) && $page > 0 ? min($page, 100000) : 1;
    }

    private function logDatabaseFailure(mysqli_sql_exception | RuntimeException $exception): void
    {
        error_log('CampusHub event storage failed (' . get_class($exception) . ').');
    }

    private function logUploadFailure(RuntimeException $exception): void
    {
        error_log('CampusHub event poster upload failed (' . get_class($exception) . ').');
    }
}
