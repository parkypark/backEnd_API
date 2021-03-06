<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
      html, body, td, th {
        font-size: 10px;
      }

      td,
      th {
        padding: 2px 4px;
        text-align: left;
        vertical-align: top;
      }

      h1,h2,h3 {
        margin: 0;
      }

      h1 {
        margin-top: .25em;
      }

      p {
        margin-bottom: 0;
      }

      table {
        border-collapse: collapse;
        border-spacing: none;
        width: 100%;
      }

      .bordered {
        border: solid 1px black;
      }
      .bordered-bottom {
        border-bottom: solid 1px black;
      }
      .bordered-top {
        border-top: solid 1px black;
      }
      .col-min {
        width: 1%;
      }

      .float-right {
        float: right;
      }

      .stretch {
        width: 100%;
      }

      .text-blue {
        color: blue;
      }
      .text-center {
        text-align: center;
      }
      .text-em {
        font-style: italic;
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
      .text-large {
        font-size: 11px;
      }
      .text-small {
        font-size: 7px;
      }
      .text-strong {
        font-weight: bold;
      }

      #sw_logo {
        height: 80px;
      }

      #wrapper {
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
      }
    </style>
  </head>
  <body>
    <div id="wrapper">
      @include('transmittals.header_architectural')
      @include('transmittals.header_transmittal')
      @yield('content')
      @include('transmittals.footer_transmittal')
    </div>
  </body>
</html>
