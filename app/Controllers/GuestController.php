<?php
namespace App\Controllers;
use App\Models\GuestModel;
use App\Views\Display;

class GuestController extends Controller {

    public function __construct()
    {
        $guest = new GuestModel();
        parent::__construct($guest);
    }

    public function index(): void
    {
        $guests = $this->model->all(['order_by' => ['name'], 
            'direction' => ['ASC']]);
        $this->render('guests/index', ['guests' => $guests]);
    }

    public function create(): void
    {
        $this->render('guests/create');
    }
    public function edit(int $id): void
    {
        $guest = $this->model->find($id);
        if (!$guest) {
            // Handle invalid ID gracefully
            $_SESSION['warning_message'] = "A vendég a megadott azonosítóval: $id nem található.";
            $this->redirect('/guests');
        }
        $this->render('guests/edit', ['guest' => $guest]);
    }

    public function save(array $data): void
    {
        if (empty($data['name'])) {
            $_SESSION['warning_message'] = "A vendég neve kötelező mező.";
            $this->redirect('/guests/create'); // Redirect if input is invalid
        }
        if (empty($data['age'])){
            $_SESSION['warning_message'] = "A vendég életkora kötelező mező.";
            $this->redirect('/guests/create');
        }
        // Use the existing model instance
        $this->model->name = $data['name'];
        $this->model->age = $data['age'];
        $this->model->create();
        $this->redirect('/guests');
    }

    public function update(int $id, array $data): void
    {
        $guest = $this->model->find($id);
        if (!$guest || empty($data['name']) || empty($data['age'])) {
            // Handle invalid ID or data
            $this->redirect('/guests');
        }
        $guest->name = $data['name'];
        $guest->age = $data['age'];
        $guest->update();
        $this->redirect('/guests');
    }

    function show(int $id): void
    {
        $guest = $this->model->find($id);
        if (!$guest) {
            $_SESSION['warning_message'] = "A vendég a megadott azonosítóval: $id nem található.";
            $this->redirect('/guests'); // Handle invalid ID
        }
        $this->render('guests/show', ['guest' => $guest]);
    }

    function delete(int $id): void
    {
        $guest = $this->model->find($id);
        if ($guest) {
            $result = $guest->delete();
            if ($result) {
                $_SESSION['success_message'] = 'Sikeresen törölve';
            }
        }

        $this->redirect('/guests'); // Redirect regardless of success
    }

}
