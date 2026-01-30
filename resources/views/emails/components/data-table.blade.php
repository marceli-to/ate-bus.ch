@props(['rows'])

<table class="data-table" cellpadding="0" cellspacing="0" role="presentation">
  @foreach($rows as $row)
    <tr>
      <td class="data-label">{{ $row['label'] }}</td>
      <td class="data-value">{{ $row['value'] }}</td>
    </tr>
  @endforeach
</table>
