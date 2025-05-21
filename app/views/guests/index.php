<?php

$tableBody = "";
foreach ($guests as $guest) {
    $tableBody .= <<<HTML
            <tr>
                <td>{$guest->id}</td>
                <td>{$guest->name}</td>
                <td>{$guest->age}</td>
                <td class='flex float-right'>
                    <form method='post' action='/guests/edit'>
                        <input type='hidden' name='id' value='{$guest->id}'>
                        <button type='submit' name='btn-edit' title='Módosít'><i class='fa fa-edit'></i></button>
                    </form>
                    <form method='post' action='/guests'>
                        <input type='hidden' name='id' value='{$guest->id}'>    
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' name='btn-del' title='Töröl'><i class='fa fa-trash trash'></i></button>
                    </form>
                </td>
            </tr>
            HTML;
}

$html = <<<HTML
        <table id='admin-subjects-table' class='admin-subjects-table'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vendég</th>
                    <th>Életkor</th>
                    <th>
                        <form method='post' action='/guests/create'>
                            <button type="submit" name='btn-plus' title='Új'>
                                <i class='fa fa-plus plus'></i>&nbsp;Új</button>
                        </form>
                    </th>
                </tr>
            </thead>
             <tbody>%s</tbody>
            <tfoot>
            </tfoot>
        </table>
        HTML;

echo sprintf($html, $tableBody);
