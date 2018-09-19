<h1 class="text-red">{{$title}}</h1>

<table>
  <tbody>
    <tr class="bordered-top">
      <th rowspan="2" class="col-min text-large">Sent To:</th>
      <th colspan="3" class="text-large">{{$customer->customer->name}}</th>
    </tr>
    <tr>
      <td colspan="3">{{$customer->customer->address}}</td>
    </tr>
    <tr class="bordered-top">
      <th class="col-min text-large">Attention:</th>
      <th class="text-large">{{$customer->contact_name}}</td>
      <th class="col-min text-large">Phone:</th>
      <td class="text-large text-nowrap">{{$customer->customer->phone}}</td>
    </tr>
    <tr>
      <th class="col-min text-large">Subject:</th>
      <td class="text-large">{{$project_name}}</td>
      @if($customer->customer->fax)
        <th class="col-min text-large">Fax:</th>
        <td class="text-large text-nowrap">{{$customer->customer->fax}}</td>
      @endif
    </tr>
    <tr class="bordered-top">
      <th class="col-min text-large">Date:</th>
      <td class="text-large">{{date('D, F j, Y')}}</td>
      <th class="col-min text-large">Project:</th>
      <td class="text-large">{{$project_number}}</td>
    </tr>
  </tbody>
</table>

<p class="text-small">
  The information contained in this message is confidential and is intended only for the use of the individual or entity named above. If the reader of this message is not the intended recipient or the employee or agent responsible to deliver it to the
  intended recipient, you are hereby notified that any dissemination, distribution or reproduction of this message is strictly prohibited. If you have received this communication in error, please notify us immediately by telephone, destroy / delete the
  message. Thank you.
</p>

<h4 class="bordered" style="padding: 2px 4px">Comments</h4>
