@props(['rows'])

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
  @foreach($rows as $row)
    <tr>
      <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 140px; vertical-align: top;">
        {{ $row['label'] }}
      </td>
      <td style="padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
        {{ $row['value'] }}
      </td>
    </tr>
  @endforeach
</table>