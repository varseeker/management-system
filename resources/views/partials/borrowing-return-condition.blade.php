@if($borrowing->return_condition == 'good')
<span class="badge bg-success">Baik</span>
@elseif($borrowing->return_condition == 'minor_damage')
<span class="badge bg-warning">Rusak Ringan</span>
@elseif($borrowing->return_condition == 'broken')
<span class="badge bg-danger">Rusak Berat</span>
@else
-
@endif
