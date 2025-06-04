<?php
echo <<<HTML
        <form method='post' action='/rooms'>
            <fieldset>
                <label for="rooms">Emelet</label>
                <input type="number" name="floor" id="floor"><br>
                <label for="rooms">Szobaszám</label>
                <input type="number" name="room_number" id="room_number"><br>
                <label for="rooms">Férőhelyek</label>
                <input type="number" name="accommodation" id="accommodation"><br>
                <label for="rooms">Ár</label>
                <input type="number" name="price" id="price"><label for="price"> Ft</label><br>
                <label for="rooms">Megjegyzés</label>
                <input type="text" name="comment" id="comment">
                <hr>
                <button type="submit" name="btn-save">
                    <i class="fa fa-save"></i>&nbsp;Mentés
                </button>
                <a href="/rooms"><i class="fa fa-cancel">                    
                    </i>&nbsp;Mégse
                </a>
            </fieldset>
        </form>
    HTML;