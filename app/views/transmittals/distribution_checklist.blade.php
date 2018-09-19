<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
      html,body,th,td {
        font-size: 10px;
        font-family: 'Droid Serif';
      }

      hr {
        border: none;
        border-top: solid 1px #888;
      }

      h1 {
        margin-bottom: 0;
      }

      table {
        font-size: 11px;
        border-collapse: collapse;
        border-spacing: none;
        width: 100%;
      }

      td, th {
        padding: 2px 4px;
        text-align: left;
        vertical-align: middle;
      }

      #sw_logo {
        height: 80px;
      }

      #wrapper {
        clear: both;
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
      }

      .bordered {
        border: solid 1px #888;
      }
      .bordered-bottom {
        border-bottom: solid 1px #888;
      }
      .bordered-top {
        border-top: solid 1px #888;
      }

      .col-min { width: 1% }

      .column {
        display: table-cell;
        min-width: 25%;
        max-width: 25%;
        width: 25%;
      }

      .container {
        display: table;
        width: 100%;
      }

      .text-nowrap {
        white-space: nowrap;
      }
      .text-red {
        color: red;
      }
      .text-right {
        text-align: right;
      }
    </style>
  </head>
  <body>
    <div id="wrapper">
      @include('transmittals.header_architectural')

      <h1 class="text-red">Distribution Checklist</h1>
      <hr>

      <table>
        <tr>
          <th class="col-min text-nowrap">Project Name:</th>
          <td>{{$project_name}}</td>
          <th class="col-min text-nowrap">Project #:</th>
          <td>{{$project_number}}</td>
        </tr>
        <tr>
          <tr>
            <th class="col-min text-nowrap">Contract Manager:</th>
            <td>{{$contract_manager or 'NA'}}</td>
            <th class="col-min text-nowrap text-right">Date:</th>
            <td>{{date('F j, Y')}}</td>
          </tr>
        </tr>
      </table>

      <h4 class="bordered" style="padding:2px">Document</h4>
      <div class="container">
        <div class="column">
          <div class="field">
            <span>{{$preliminary or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Preliminary</label>
          </div>
          <div class="field">
            <span>{{$final_submittal or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Final Submittal</label>
          </div>
          <div class="field">
            <span>{{$addendum or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Addendum #{{$addendum_number or ''}}</label>
          </div>
          <div class="field">
            <span>{{$construction or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Construction</label>
          </div>
          <div class="field">
            <span>{{$as_built or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>As-built</label>
          </div>
        </div>

        <div class="column">
          <div class="field">
            <span>{{$commercial_glazing or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Commercial Glazing</label>
          </div>
          <div class="field">
            <span>{{$contractor_markups or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Contractor Markups</label>
          </div>
        </div>

        <div class="column">
          <div class="field">
            <span>{{$thermal_values or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Thermal Values</label>
          </div>
          <div class="field">
            <span>{{$leed_submittal or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>LEED Submittal</label>
          </div>
          <div class="field">
            <span>{{$engineering_review or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Engineering Review</label>
          </div>
        </div>

        <div class="column"></div>
      </div>

      <h4 class="bordered" style="padding:2px">Distribution</h4>
      <div class="container">
        <div class="column">
          <div class="field">
            <span>{{$document_control or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Document Control</label>
          </div>
          <div class="field">
            <span>{{$august_duque or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>August Duque</label>
          </div>
          <div class="field">
            <span>{{$grace_llavore or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Grace Llavore</label>
          </div>
          <div class="field">
            <span>{{$kurt_leano or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Kurt Leano</label>
          </div>
          <div class="field">
            <span>{{$drafter or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>
              Drafting
              @if(isset($drafter_name))
                ({{$drafter_name or ''}})
              @endif
            </label>
          </div>
        </div>

        @foreach($distribution as $group)
          <div class="column">
            @foreach($group as $person)
              <div class="field">
                <span>
                  <?php
                    if (isset(${str_replace(' ', '_', strtolower($person))}) && ${str_replace(' ', '_', strtolower($person))}) {
                      echo '&#x2612;';
                    } else {
                      echo '&#x2610;';
                    }
                  ?>
                </span>
                <label>{{{$person}}}</label>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>

      <div class="container bordered-top">
        <div class="column">
          <div class="field">
            <span>{{$john_derose or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>John Derose</label>
          </div>
          <div class="field">
            <span>{{$laine_funk or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Laine Funk</label>
          </div>
          <div class="field">
            <span>{{$roger_huitema or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Roger Huitema</label>
          </div>
        </div>

        <div class="column">
          <div class="field">
            <span>{{$contractor or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Contractor</label>
          </div>
        </div>
        <div class="column">
          <div class="field">
            <span>{{$info_only or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Info Only</label>
          </div>
          <div class="field">
            <span>{{$transmittal_only or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Transmittal Only</label>
          </div>
          <div class="field">
            <span>{{$submit_electronically or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Submit Electronically</label>
          </div>
          <div class="field">
            <span>{{$hard_copy_office or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Hard Copies to Office</label>
          </div>
          <div class="field">
            <span>{{$hard_copy_site or false ? '&#x2612;' : '&#x2610;'}}</span>
            <label>Hard Copies to Site</label>
          </div>
        </div>
        <div class="column">
          <div class="field">
            <span>{{(isset($days) && $days == 2) ? '&#x2612;' : '&#x2610;'}}</span>
            <label>2 Days</label>
          </div>
          <div class="field">
            <span>{{(isset($days) && $days == 5) ? '&#x2612;' : '&#x2610;'}}</span>
            <label>5 days</label>
          </div>
          <div class="field">
            <span>{{(isset($days) && $days == 10) ? '&#x2612;' : '&#x2610;'}}</span>
            <label>10 days</label>
          </div>
        </div>
      </div>

      @if(isset($comments) && strlen($comments) > 0)
        <h4 class="bordered" style="padding:2px">Additional Comments</h4>
        <p>{{$comments or ''}}</p>
      @endif
    </div>
  </body>
</html>
