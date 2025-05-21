<?php
namespace App\Controllers;
use App\Models\RoomModel;
use App\Views\Display;

class RoomController extends Controller {

    public function __construct()
    {
        $room = new RoomModel();
        parent::__construct($room);
    }

    public function index(): void
    {
        $rooms = $this->model->all(['order_by' => ['floor, room_number'], 
            'direction' => ['ASC']]);
        $this->render('rooms/index', 
            ['rooms' => $rooms]);
    }

    public function create(): void
    {
        $this->render('rooms/create');
    }
    public function edit(int $id): void
    {
        $room = $this->model->find($id);
        if (!$room) {
            // Handle invalid ID gracefully
            $_SESSION['warning_message'] = "A szoba a megadott azonosítóval: $id nem található.";
            $this->redirect('/rooms');
        }
        $this->render('rooms/edit', ['room' => $room]);
    }

    public function save(array $data): void
    {
        // if (empty($data['name'])) {
        //     $_SESSION['warning_message'] = "A szoba neve kötelező mező.";
        //     $this->redirect('/rooms/create'); // Redirect if input is invalid
        // }
        // if (empty($data['year'])){
        //     $_SESSION['warning_message'] = "A szoba éve kötelező mező.";
        //     $this->redirect('/rooms/create');
        // }
        // Use the existing model instance
        $this->model->floor = $data['floor'];
        $this->model->room_number = $data['room_number'];
        $this->model->accommodation = $data['accommodation'];
        $this->model->price = $data['price'];
        $this->model->comment = $data['comment'];
        $this->model->create();
        $this->redirect('/rooms');
    }

    public function update(int $id, array $data): void
    {
        $room = $this->model->find($id);
        if (!$room) { // || empty($data['floor']) || empty($data['year'])
            // Handle invalid ID or data
            $this->redirect('/rooms');
        }
        $room->floor = $data['floor'];
        $room->room_number = $data['room_number'];
        $room->accommodation = $data['accommodation'];
        $room->price = $data['price'];
        $room->comment = $data['comment'];
        $room->update();
        $this->redirect('/rooms');
    }

    function show(int $id): void
    {
        $room = $this->model->find($id);
        if (!$room) {
            $_SESSION['warning_message'] = "A szoba a megadott azonosítóval: $id nem található.";
            $this->redirect('/rooms'); // Handle invalid ID
        }
        $this->render('rooms/show', ['room' => $room]);
    }

    function delete(int $id): void
    {
        $room = $this->model->find($id);
        if ($room) {
            $result = $room->delete();
            if ($result) {
                $_SESSION['success_message'] = 'Sikeresen törölve';
            }
        }

        $this->redirect('/rooms'); // Redirect regardless of success
    }

}
