<?php
namespace App\Controllers;
use App\Models\GuestModel;
use App\Models\Model;
use App\Models\ReservationModel;
use App\Models\RoomModel;
use App\Views\Display;

class ReservationController extends Controller
{

    public function __construct()
    {
        $reservation = new ReservationModel();
        parent::__construct($reservation);
    }

    public function index(): void
    {
        $reservations = $this->model->all([
            'order_by' => ['date'],
            'direction' => ['ASC']
        ]);
        $this->render('reservations/index', ['reservations' => $reservations]);
    }

    public function create(): void
    {
        $this->render('reservations/create');
    }
    public function edit(int $id): void
    {
        $reservation = $this->model->find($id);
        if (!$reservation) {
            // Handle invalid ID gracefully
            $_SESSION['warning_message'] = "A foglalás a megadott azonosítóval: $id nem található.";
            $this->redirect('/reservations');
        }
        $this->render('reservations/edit', ['reservation' => $reservation]);
    }

    public function save(array $data): void
    {
        if (empty($data['days']) || empty($data['date'])) { // meg kell változtatni --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
            $_SESSION['warning_message'] = "Hiányos adatok!";
            $this->redirect('/reservations'); // Redirect if input is invalid
        }
        if (!is_numeric($data['days'])){
            $_SESSION['warning_message'] = "A megadott értékek típusa nem megfelelő!";
            $this->redirect('/reservations');
        }
        $reservations = $this->model->all();
        $currStartDate = date_create($data['date']);
        $currEndDate = date_create($data['date']);
        date_add($currEndDate, date_interval_create_from_date_string($data['days'] . ' days'));
        foreach ($reservations as $reservation){
            $startDate = date_create($reservation->date);
            $endDate = date_create($reservation->date);
            date_add($endDate, date_interval_create_from_date_string($reservation->days . ' days'));
            if (!($endDate < $currStartDate || $startDate > $currEndDate) && $_POST['room_id'] == $reservation->room_id){
                $_SESSION['warning_message'] = "A foglalások időpontja nem ütközhet!";
                $this->redirect('/reservations');
            }
        }
        $this->model->room_id = $_POST['room_id'];
        $this->model->guest_id = $_POST['guest_id'];
        $this->model->days = $data['days'];
        $this->model->date = $data['date'];
        $this->model->create();
        $this->redirect('/reservations');
    }

    public function update(int $id, array $data): void
    {
        $reservation = $this->model->find($id);
        if (empty($data['days']) || empty($data['date'])) { // meg kell változtatni --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
            $_SESSION['warning_message'] = "Hiányos adatok!";
            $this->redirect('/reservations'); // Redirect if input is invalid
        }
        if (!is_numeric($data['days'])) {
            $_SESSION['warning_message'] = "A megadott értékek típusa nem megfelelő!";
            $this->redirect('/reservations');
        }

        $reservations = $this->model->all();
        $currStartDate = date_create($data['date']);
        $currEndDate = date_create($data['date']);
        date_add($currEndDate, date_interval_create_from_date_string($data['days'] . ' days'));

        foreach ($reservations as $currReservation) {
            $startDate = date_create($currReservation->date);
            $endDate = date_create($currReservation->date);
            date_add($endDate, date_interval_create_from_date_string($currReservation->days . ' days'));

            if (!($endDate < $currStartDate || $startDate > $currEndDate) && $id != $currReservation->id && $_POST['room_id'] == $currReservation->room_id) {
                $_SESSION['warning_message'] = "A foglalások időpontja nem ütközhet!";
                $this->redirect('/reservations');
            }
        }
        $reservation->room_id = $_POST['room_id'];
        $reservation->guest_id = $_POST['guest_id'];
        $reservation->days = $data['days'];
        $reservation->date = $data['date'];
        $reservation->update();
        $this->redirect('/reservations');
    }

    function show(int $id): void
    {
        $reservation = $this->model->find($id);
        if (!$reservation) {
            $_SESSION['warning_message'] = "A foglalás a megadott azonosítóval: $id nem található.";
            $this->redirect('/reservations'); // Handle invalid ID
        }
        $this->render('reservations/show', ['reservation' => $reservation]);
    }

    function delete(int $id): void
    {
        $reservation = $this->model->find($id);
        if ($reservation) {
            $result = $reservation->delete();
            if ($result) {
                $_SESSION['success_message'] = 'Sikeresen törölve';
            }
        }

        $this->redirect('/reservations'); // Redirect regardless of success
    }

}
