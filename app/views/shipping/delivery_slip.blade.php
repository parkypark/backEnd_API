<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <style>
      table {
        border-collapse: collapse;
        margin-bottom: 2em;
        width: 100%;
      }

      table:not(.borderless) thead th {
        background: #ccc;
      }

      table tr td, table tr th {
        border: solid 1px black;
        padding: 4px 8px;
        text-align: center;
        vertical-align: top;
      }

      table.borderless tr td, table.borderless tr th,
      table.signature tr td, table.signature tr th {
        border: none;
      }

      table.signature tr td {
        border-bottom: solid 1px black;
      }
      table.signature tr th {
        vertical-align: bottom;
      }

      #wrapper {
        max-width: 1100px;
        margin: 0 auto;
      }

      #header {
        width: 100%;
      }
      #header tr td, #header tr th {
        border: none;
        border-bottom: solid 1px black;
      }
      #header h1 {
        margin: 0;
      }
      #header img {
        max-height: 100px;
        width: auto;
      }

      #photos td {
        text-align: center;
      }
      #photos img {
        max-width: 100%;
        height: auto;
      }

      .text-bottom {
        vertical-align: bottom;
      }
      .text-left {
        text-align: left;
      }
      .text-right {
        text-align: right;
      }
    </style>
  </head>

  <body>
    <div id="wrapper">
      <table id="header">
        <tr>
          <th class="text-left text-bottom">
            <h1>Delivery Slip</h1>
          </th>
          <td class="text-right">
            <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wgARCABkAPADAREAAhEBAxEB/8QAHAAAAgMBAQEBAAAAAAAAAAAAAAYFBwgEAwIB/8QAGwEBAAIDAQEAAAAAAAAAAAAAAAMEAgUGBwH/2gAMAwEAAhADEAAAAdUgAAAAAAIe3qPmotgAAAAAAAAAAAAAAAAAKmwrw1qOxNLcAAAAAAAAAAAOP5LXFfo5jOn1fYoLC8rRbR0m03V9ijcbLRJrKm7TmHvkOrTKXRtMmrkMq7lLp6Cpd5dFvjZ7Ojw4z0JT7u97nDK8ezTYtxb1nka7g6GyrHOUPS7mzbPNKsW0sKfQZuoejO82kt+zyX39xy3rvT9a7PyhNh3FbwdHOza1G9B86dPOvS+D5P8Aj6/TaFqk1eX9f6bqzZeWAtR7GkKnbaUv+cV9Bv6rrdTZ9nmJvKkzSa3Ius9atqzydrWeWqOt1lwWuRoCj3t8XeFVItrWVfpbsucWiQ734+fUD7tbF6jzKoON9j1LsfL8u670/Tux8zrev0fF8mdpdL1fYqsr9Q7S6V8n0VZ1+lsWfncs671HXGz8mrOv0qfFt7Gn53k+S++75hBsW/WxS9ZqsJHf8c/nxLDyTR8EuPCltnSycOFjtkr8TOQzrQHy55/Xxl8k5aS8tekkPzlhxJZ77T+MJpmLHkrX+iKTsjytLVE+tvGWTWq0e0bJdSrfbsfYh/I5mWxp0KPdwPW8ajQ7uxYaEDBtPrDOVxjemr6I/q3W3XRHlPKvTgjK20YsK0djZksq8jlWjsbPP8k7fsDBJQzpQ9Dty1yWcKHo2mth5p8vtUVuqsKfn3aXS5m1/pUnueXsqhFYM+gzpQ9Dt61yVWV+osKbn6+h37zLpLGsc7kzWeral2PmGdaPoT9NoVzDYu0ulsexzuWNd6he9zhovGzYk/P0pU7Ty+ZRGNtwl1Fr2eVyRrPWNbbPydFh3jHa0uadT6ldNvjLDn5/Jes9X1ts/J8sa31DQV7gcy6/0rX+08jVYtpQ9PutRbHzHImr9a0ff86zdQ9G1RsvLojC37/cKdq9e6S6a07PLqEW2YM6CzHsnCXUQmF2vYegtizytZ1+kgev4p95veMMUMd8spEO6Y5NdIfYGuXVJ0W4/CJwt2DPz/v9wQoN6+z6JFh3j1No1qPZRWNrz+ZOMun7coQAAAACkeq1d3crtAAAAAAAAAAAAAAAAAAPLL5WO+o2nz98AAAAAAAAAAAAAAAAABbvQTNWXrjyAAAAAAAAAAAAAP/EACgQAAICAgIBAwQCAwAAAAAAAAQFAwYBAgAHFREUQBASExYiMCUmNv/aAAgBAQABBQL+uvl5aNfmWlt4VFQAshVf4RZUYIqhkztXDt2iQeruJ3oErZqzeWW0ta8cN58pVT2ZrhXcLbhDHXWWzZNfTMMHLDQ9GlrNjdWMt6Y6RA1Sz62McnWXeFxcmyxsBAwj24ZGRLFFcnJLgKIqLW0mHKgapd52TIyOeSFCa8aFkaybw2G3NUTIYd1KOwZPlzl0u8sqi3f0vIHY8BmqwGBeuXr4FQn/AFdwzj11KnjrKy3ocg1vrs7TSu13XZ4b2Wx/Ato/uFSc4428QVisR1scwrUISnDbO7V9LGy8Ul61Xe4acd/5J9fkWVbBXbNDKzVwcr0UsmsMS3Mj60MrCczbygwTlWB97O4jlQmxXgIM1ktFyEut7mIBD1oNFgls7ESjpVgloislZUiJaFvBPqmW6Jt7cd5yygRQjiLC/Z3cFjEx52G30hTdbQQirXVsXpoK1uTKn7Kb6Sa0IaISvklwB6VRqKzasQImgQKcoexcvjfQNF1mPDFoeWpBYVlnO/JKbHDykzZ32gtBKzH78w355UjPMtTtclWBsTJDam4GxlyaFSD2neQs9mnJYZZpMcHtK8HUixqCZcXsOPXNjRc/ZUPM2hFjm3Y4n483FNjg/YK8GQvskM3ml/Wx747X9eY7H0lI07CLk5l3ibfU/Tfms2u3AY9IhC7uuBmFa6FhS3pZBMOx1JGjsKxqUxnQKZAnKlhwxZANBmwroCOyz5R1eK2tDiX9aKzYDaxXFmRK9WjdAa9Uiideu0OvNaCh14TBUwSN4U40KoKuN4WAyhLCusqc2do+GTcAtwTPZvYBknFzDRlAysIaubaw4h0HfgGF87Qh9GA7eMKsWaMsdq6caq16FT4oTs5j/KkrvHV2WTWGJuhmZJa7qPbRmOuGVr52Yy/KbVF3i0PaA8eslHZSMq/frRLrPSq/ooWcVl+wup8PuQes5PR3cCpWmnV038pv9nc8DebILcutqpnxDW/FPedoD/cDUopbBB2hB9plUHlb/QnP7VcMY9MXCfbx2+8AOjoCemWKo7ZY7ZzjXAeubVcOXZp555XFXhU7QPP7hyzOdUiihIJGDPi0mZJZ1iLASipklwnq1sSkCzusoVJqZc/hsnX8S8LrouclHyzos2FeCFEuEslb1sXI49YY2oRh0amgzpjMaTe2Op7BgdcJihoGVYjaIlS/RUudLTGkKeikIyikLBjqmrC9H9H9WDsGBwnQ0W1M1YFQwRjRcW1SIF9yuVaNDNxygHfcERGqNTq+a61DDhXjf0x6avL78yWTWGLr4XbxnzLJnOtfEH0DF+L/AP/EAEIRAAEDAgIDDQYCCgIDAAAAAAECAwQAEQUhEjFRBhATFEFhcYGRocHR4RUiMjOx8EDxIyUwQlJTYnKCkiRDNVTi/9oACAEDAQE/Af2bStNa1dX419zg2yqoidFofg2mlPuBtGs1LjRMLs0tPCLI22A7KZEOasM6PBqOog3HWD51icNuC8GUG+WfTSYkONES9LuVqzAGysNwuHiDRdKCnPb6U57NakFooVog2vpeFvGsXjMRJHAsbM6wjCePnhHckDvrEI4iSlsp1CpatJYb2Z1hrUd1TcZxBJOV7+FqxLDoGHNhZBJPJf0qCzAnOhgpUknnv4ViuGHDlixuk6qbKAoFwXHZUTB4UmOJCklN+f0p9cZQswgjnJv3W3mVNpVd1Nx028DSsHgtxuMuAjK9r91PKZUf0KNHrv4CsLZjynQw6g3PLfwt41iuCNxmOHjXy19FMqbSq7qbjpt51OZw+K2goSoqWL69V+qmykKBWLisPwmHOYD5SU35/SnHICVlKGiR/d/81HjYdIjOPgEFA1X7KhSOKyEPW1UsYbjNs7q7FU/udcZPCRV3tyHz/KpL7kh5Tr3xVIkOSnOEc11/4rDOdI7z6mhlTSFYk+t982TrUdn3yVhM7h53Bo91GjZI++Wt0qNCUHOQimf0qlOHlNvvqrc3H031Pn936msb4KVJDZeCdHkN9fZbZTDEfBFB9+6jyEDL61ieJqxFYNrJGqmWi84ltOs5Vi7ghYfwaOX3R99G/h0fjUpDXJy9FbpJHBx0sD976Dehf8aG9KOs+4OvX3VgM4SWTFd1p7x6VKwpTM9MYfCo5dHpWJv8YlrUNWodApKStQSnWak6MGBwYVbK1/yvUfD48aMuctYcsMtl+e/lSX3ENqaScla+qoEHhsMWP3nDl/j9mnGnGVaLgsawR59lhbktVmxqv4ffRUl0PPLdHKSawiGt+WjSGQz7K3SOL0EMpGvOokJ6YsIaHXyVMkvYYrikb3Ujlt8R21huJTXZSEqN032DyrdPGU+yhSRquO2mWSkpaGsXNYSxxKCCsZ/Efvop9S3HC45rOdSWuGwrQRn7op+OuPYOazyco6a3PxFLk8MoZJ+tbo1rdfS0kZJH1qHhUmYqwTZO0/edYkGkyS2wPdTl2a++tzcQpK5CxzDx8Kx1xbsxVxknKm2nHjotpuaxWK9GjsM6PugXJ5zrqO+uM6l5vWKflsuQuPp1gZcxOW9gURT0sOKGSc/Kt0ri1FthINtdMNTX2eKNNmxN/s6qxKM3AQ3G1r1qP0FKfdGWicuek4ziKBYFXbT2IyXvnAnpNcbX/B317YxHar/Y0cXxLartNIxSe0nRb0gOuncTnPCztz03pvFZzI0WyUjmv50MbxC4u6e0+dImFlwusu2Jr2vJ/wDYPaadnreN3XNLpvSMVeaTooesOYmjMBNyod9e2pP889qq9tyf557VV7bk/wA49qq4/npXz6/Kvbsr+af9lU5jDzw0XFFQ5yabxd1j5RI6CRSsdkqGiVKt/ca4/wD00MTc0ODAOjsvlXG1H/rNJxaakBKdO3Sa9rztq+017Vn/AMS/9jSnFvHTcNzz01gsp9Om3Yjpp2Kpp0NFQv05dZpOBy1p002I6acjltwN6QN9hy7aewh1tvhXCm3SM+io+DKlZtaN+kXp7BHY/wA0pT/lam4wdXoBVuk0Nz0xSNNBuOYg1NUUpDZqLuZflNhaFA9Yyp7CkMK0Cq/QQaY3NqkfKUD/AJCndzSmDouqAPOoU7uedZRwpRdO0G/0ribOyuKM/wANM4C44jhCgJTtVlScGaWrQQ42T1+VScNVDVovN2qLAElWg2Eg89hT2CPxU6buiB01FgOTPlEX2Xz7Kfwl+MLulI66iQHZvyrdudSI6oy9BRBPMb1Gw9+SkuJySOU5Chh+mdFt1Kjsv5gCnIEllsuuIsNWe9uZXdlxGw/f0pyIt6euO1/EfrWGqYcjllj4U+707T31DiGS9wasgPiOwCp8vjTnuZITkkc1bmY/xyD0efhWNSOMTV7E5dnrSUlaglOs1EnNxpQw7kAAv/Vy9v1rdJhIS8JKfhJ7/Wo54thzrvKshI+p8t7c1H0WlyDy5dn33VikjjUxa+TV2VuYcUQ6g6hbxrG46Y0whGo51gOGIKONvC+zzrGsQVLfLaT7ifu+9Ja4fCv02sIv1gUwvg3UL2EVulTeKk/1eBrCGkRiiU9rUdFI7ieqt06PlL6fCkfqyLwn/a4MuZO3r3noIn4a20wq2o/f3rqRhMyNmtGXNnU7EeNRGWb3I1+G9uZcs843tF+z86xZSIC3Q38x3uT6mtzC7tuI2Ed/5ViriImnEZ1qN1H6J6t5v9V4Zc60jvPrWusIQOGMhfwtjS8qceUXNM6yfWob7eMwi27r1HzrFhxcNQgb6Az6TnQF8hTx9l4bYawLdZ9c97BYvEYhceyJzPRWIyuOSVPDVydFRXv1YlxrkR3gb2GwzNkpb5OXorHZ6Y7BYT8SvpvSW0TYCS8bCySfGpM4vSQ6kWSn4RsArFW2Vspdf+FBv083WalSFy3VPOazWGQuPyA0dXLTMyVAUUtqtbk9Kw7H1yHUsPp18o8q3RNNtSwUDWLnewyd7PeLtr5Wp95chwuuazWHYicP4QpFyoUpRWSpWs1FeYYOk63pEc9vCpWPNzGuCdZy/u9KujTvb3dnr6Uxi8aO0WURsjr97X3ViLiFLPApsNl76/yrDJq8NcC057eepUgyn1PK5ahyWIyg4tvSUDtt4VMxxqc3wbrOX93pTU+NHOkzH97aTfyqZicmdk6rLYNW9AxR/D8kZpPJTj0BxWnwSk8wULfShjBjt8FDbCB2mlrU4orWbk70nFVvxG4gFgNfPbVvYjiipyUN2sE/Xehz3YOkWtZ5dlOzo8s6clr3tqTa/Ub0xPYhHTjNe9tUb9wtTzy31lxw3J/ZD9LI6PxpNs6iJ90rPL+Ne+WqkjRFh+G//8QAQREAAgECAwMHBQ4GAwAAAAAAAQIAAxEEEjEFECETFSJBkaHRFkKBouEGFCMyQENRU2FxscHS8DAzNFJUkkRj8f/aAAgBAgEBPwH+HjKYoUaNLrtmPp/8+W7Pw/vrErT6uv7ptiryuMa3Vw+Rk2FzFZqnEcBDmTjrKbFxczMzNZZUqMhtByhF7ykxYXMq1MnARGzLebHpcjh3xPW3RH7+/wDCYmoXqPVHC/GU3dzHLoLynUzw/ZGqurWgDdZ3G/VOVctlEF+uVCyi4lOsWNmhv1RC7GGPVZDaAP8ATCzqwEYZhafCUoK4PBooAFhAAosJ/MqbieTFhKqWS/XMKC/QXWY22Ep08MvmKSfwHrGYhuFpRuq3tCTW4CU6eSE2F5SGZ773bKt5QW5vufpMFlZMpzCLUumaUxZdy9N7xnZmyaSwJvHe1QfZAQdJWAJsusUWFpVayzDjiTGcLrEUVOk0qU1CmYKtyFUOOrjMXiffC1sR1MQo+7X8hKpzvBYCwim1SBs2krt0bShYC8aoqynfLczEN5soiywkDWU2DMTGGYWMCkPk3VmstphwOJhKA5iZTYuS0p4TDuLmuB6DBsvBNriF7PbF2RhfMxA9C+2cz0BrW9UzmjAddcdkGyNnf5A7pzRs061x2rF2Ns7qrd6zmPZ7fOd48I+wMEEYpcn0eEXA1moijUotwJP4Tmk/Ut3QbJcaUm7pzOx+ab1ZzPU+rb1fGcxt9WfV8ZzE31Z9X9U5hb+w+r+qcxP9vq/qnk7+7D9UHucto3cPGeTebWp3e2eTQ+s9X2zye/7e72w+59RxNYdnth2NSGuIX9+mHY+G/wAhO7xh2VhR/wAhZzdhR8+vZGUISq6Q1lGsDXF5yyiBri8TEspshIjbRxC8DUbtMGOdvt9AM99uNVH+q+E5wp6NSXsP5GbCpK9Z8QosBw8ZX903J1CqJwnlFiG0Qd/jG90uJGqjsPjPKTFNoB2TyhxTcLjsh21jv7+4Q7Yxx+c7h4RtsYrTlTDtTF9btBtHEvpVPaY+OxHnVG7TPfTVPOJjVMusFUNpGcJrFObjGcLwmf6RA6k2G7EaiBrJmMqXDXMZsovEXKJiG0WUlypuZCy8pNm7RfCq6DrH7PojdJwN2IbjaU1yqJiBoZRbMsrVPNEpJlF9ym1ThDxEw/xpVOboiYfrn8xrdQ3B8lQkxait1xEysTuxA4Aynd7X0ExGoMpjN0juPwlTdVPC30xKRdWI83xtGBpPcSl0rvuHwlTdVbO1hEXKtow+Esd1Rsi3lFLm+5SUfhFSy2lMm9hFXKLCVHyLeFVfWPRsLiUCSu6omcWgGUWEqU89tzAnQxaJU3BnG0NJib5psanwBf6b/wCo8WExNsQ7Na1zFGUWjKW4AxaJQ3BhRm1MWmqabnph4A465yVzdjeablp2YtuSnk47mQPrAhX4phQv8YwAAWH8JicJszjq/D879nD5aqliFE2zUHLLh10QW+W4H+qp/eJUc1HLtqfk3//EAE0QAAIBAwEDBggICA0FAAAAAAECAwAEERIFEyEiMUFRcZEQFCMyUmGBoQYzQnKxwdHhFSRAU2KSwtMwNDVDgpOUorLS4vDxNmNzdKP/2gAIAQEABj8C/g9sXY+IWRbWP+hnPvb8turkHEgXTH848B9tWupdLyapCO08Pdj8jluJjpijXUxqS4hnXZtkj6VCoHkbtzwp7oTjacEY1SRyIEkA9RHD3U93NEkKmQiNV9Gp7XZm6S0t+RJPKuRq6aW2Fzb3JKaydzpx76juVurYTyJrEBg92c/VRurwKCzkJpXHAffmlht9L3r8cHmQdZq1unxrdeVjrBxVlssHMUAN1OOwE47gf1q3kV7EsdrbgaZIdRYgdeempIhLBAka6mk3OfZz014txa3cSY1q0JQ9XpVJmLdTxY1gc3HpFMIZFil6HZdQHsqWyjlguShC6lixk9XPQa8u4php+Ljh04PbnwYtplgkz5zx6/dkV4hA9vLmXdrIIuBGfO5/bTeNXCXDdBSLRj3mpb23uYVjQDyTxZJOevNeKX+7Uv8AFsoxyuqsW0ywSek6a/dkVciWe2jt7aYxMyx51kc+ONMIXEUnQ7LqA9lPaCe3uCoBLbnTz+2o3lv4IpGXJTxXOn+9VjZtLbyxXT4WRYscOnhmrm01aDIuA3rpxu3SDVxyuuI+2vF9o225VxpMsRyO7/mobe1PkFXktnOc8c0sEIwi8cnnJ6zX6E83/wAx/pFEZx6xVvZ2cZmuG8nbw9LHrPq6TQmkbxi9acSXE/XwI7uarjePpW3kYnPyVxn7av8AacvPeXSW0Y/R1B2H6i1BZrzztqb5o+/HdUk42ZPcb9tW8jZOKjm4Zz101lZ+L2iKcyxyyHecOsYqQCTfTS41vjHsFTXD+ZEhc+ylnl46GNy/bn7SPDdXAbS4TCfOPAVNeMOTAuF+cfuz4NmbNB5EZ8bmHqHm++l2lbciKZsnT8iT76mv2x4xbod4n6XR31axuPKsu8fr1HjTyOcIo1E0JzA0+qXfvEmPNznHHHZVtseK1m2dvHBd3bDlBxOMdhqC4dNU0Grdtnzc89WjYdrazTy2kebr6T7q3kEqTRn5SHIq1t9mRJJfuTvFt/rq1t2OpoolQkeoVc6JUM0o3Srq48ef3Vd3ckiKVURqCevn+imluZQCBwjB5Tdgo7S2gVubiXzYlcgQKPk8DV28cSwzKmpCZT9Zramz7iXdLdw4znHWPrrY+z5JE1W8Ul1I3QXPJH+Ju6nWJwUUiCNieHf25qKCBgY4lCDBreyNufxpw+o4xkkVKYAzRI2kTfJfr09dC1jkVpJ2wQDzKOP2VcXLyoJJnxjVzAf8mmJmWeb5MMbZJ7eqori8ctNPmU55lB5gPZVrYxSK3Heyafd9dQ4dTLMTKwDf76MVrnlSFet2xW1bszrv5ZAkcbcDuxzY7amtZhmOQYNfgaQsElkCzKvM6A6s9w8EsKON9Od3gNxA6fs9tXd3JJGHJESgtxA5z9XdX4TuLyMTJFuguoHhnoHPmrvaB1xWfxMERPex9dShNgSygnGsSJy6yfgUxPqwPoFERfBUWQ6fKiP9muRsZG+bdq31VyfgZAeyMH9msj4Fxg/+qf8ALRkm+CSSP6ctozH6KLQfByC2Y8CUtHWg9zsKylcDGqa0YnHtNQJPsjZdvCzhXc2mNIzz08/j+zZozGkYjuE16caub9avP2H/AGaitvd7LgU8SIo3X6K3k77Jmk9N4GJ/w0FW8sQo4ABZf8tfFbI/sz/u6+I2R/Z5P3dfxTZJ7IX/AHVbvTasmMacyYx/V1/JGzj/AET+7ovb7ItInxjVEdJx/V0vjGxYLnTzbx9WO9KV0+DlqrqchlIyD+pX8lH+u/00JxsAvOBpEgbLY6s6a4fB67b5uT+zTO/wLmZ2OSxt+f8Au1/0W/tgX7K4fA3vjjqIJbraArncqANJ6uFGKcXET9TwkU9ysNwEXoaIhm7B00YZBcRyjhoaEg1JPuLhAmeTJEQx7B00bXxSWaUHSyvbZ09vVTeMbKEWG070WeFJ9RxWLOC+YZwfFxIAO400uvaBx0Q3Mrt3ZrcttTals4OCJAOT26lNWWz5Jt7K7tI7dJUebnv91Q/hG5vbeZ1zp8VwM9OCeet4ZdoIOqQBD3EUfGTtaNdWkO6YU9nJovbx7VuEBwWSPI9wrxfXMlxnG6uGZDnqrjZlu2V/tr+Tx7ZHP10LdbOO6uPzUCGU1vZPgzNHFzljbIcdozmt5Z2VlKBzjcLkdoxW9fZcWjpMNoDp9ZwOFCKztXd/+3b83rNfjEU+7AyZEiJQe2sWsVzNxAJWE4HbSm5WYKflrGSvfW9jjljTo3qac0kDs0ty/mwQrrc+yt5Ps++t4ucyNGCF7dJJpLa3uVnlZdeI+Vw9Z6PBZS+lEV7j99W1/cNyRbox9ZxzVFd3nC5uEFwU9DiQF9gArfIN9NJhIIx8tjzUd628u5jvJ5fSb7KtLEf+ZvoH11b58+byze3m92KeRzpRRqJ9VNt7l76WVpXjPRF8nux3GrO0uz+NbOkDoT/ORdK/R7q2fbcDHaRtdPnr5l+3wW9kp4RLrbtP3fTVpCfPK637TxqwmCgStrVj1gYx9NRNKS0kTGIsenHNTbMtXMageWdec/o0kzp+NzjUxPOo6F8A8UOmJrsxYXmKF8VcQ/nI2XvFTr0NAfpFXOzbU4SCIz3UnVgZVPbitoRfMYe/7q3PPsywfMnDhLL6PYPBe3N7C0rFnRvSHHnFARXapIfkS8k1tO60BIZcCEDqPFvf4LKf0JCneP8ATVgZ1Isdnjkr+dl6/YMVYzekjJ3H76ttp3a4jgiENrGezDP7fAQvmTTaQR6A6e4VgcBUdjESJr6QQDHQD5x7vpq0s9GUk8iq9AAQnj6sCkmt87rO8hPWOlfqraG12j0eNy4TPoLwFEk4A6aDMORLNrIPoDo7hjwJb2uZo4vJIE46m6cf76Kt7U4MijLkekeepoLjkCS74k+izc/cfBNPnypGmIdbUl46kW1udWfSfoHgmFrHvZQ8kKJ1niB78VLbSNvJ7gMZ5T8tm56ltrMfjN1HuVb83xGW9gzUVrCOQg5+s9dNcqFaTUFRW5if+M0kk9uk2pQVkHBsdtTXlnO2mMajFL1eo0wmYusUuhCegYHDwLbCXclZA+ojPX9tRW0C6YoxgVZh5N2kL5bhxK9IpY0UKijAUdApo7e9W0Rl0t5HW3fmlubfaa7wDHLts/tVp3i7/TjeaOGrrxn66iu5dtnexHMem3wE7BqrcPN41dbjxcMkenU0rdXzY276gsJZTvYVULcEZOR01b2iHUIlxnrPSakghvltYJE0MNzqb18c0bi22ousro5dtnh+tRjutsPuG4NHbQiPPtyazbxZl/OycW8CtLmKdeCzJz9hpY/whbXGP5yW3Or3NQuNrXku0HHMgG7QerFLFEgjjXgFUYA8F5tNm3jysWjXHmZ5/BdTahLNMxw2PNXq8FutyX3UTat2pwG7a3VhtAG2+TDdR69PYQRW62htAC16YbWPRq7SSaS3t0EcKcAo/gjpJaGzYyt1ahhAveCfy15G4Ko1Gp9oy/HX8pkPZnh9ff8Alu0McPIOPdUMEfxcSBF7B+Tf/8QAKBABAAEDBAICAQUBAQAAAAAAAREAITFBUWFxgZEQobFAwdHh8PEw/9oACAEBAAE/If8Azeybtzgn0q/rYLMpQ6RvE+A0VM1Ivc/B+jT4S4BRy9ImoN1rJgqHRQwcp5RmHRmp+RvrCLq5ZkwYofXR3tRe7pHHNEZKLNUhJ7T5og3LsLkixsUsiguMRP0eK5UkOxN3Q893G2swSF9lGCJm8+NP1FCZe+wdp5ca1J2qFG8EI5v6abRsFAoEhTdKwrcqXdHobUr5fzcE+6DLkbIDCVxYp6Nu0d35M6HwRi42iNv5FCXrsIELNEFDZU4iOrlDCVntQt3YjSnBjxibXfXTkjWk8y6VG1BVwF0+y1FtXcs3pJtMwN7ifdCVXSgiYieka60fmA1M6TGfVSI5WQEvwIM5pgL3GCUk8SUbk5Xx0di9j6pgpfOmzLuEc0argQQ1GGZmrzbJ5VdTVom7fS2/t+2ooUyNQq/sHs5bwJk/FSBlkumAbGQFQFqvYZdURlHR/QYlTiS1HwvaVAtyDogEjlt1pdL1Ly6IMTE3i9NEUrZaYDYl7r6sQBNQVpOGbxH+kfN7xn1/kGfFShMs7T8PY+HOCgxwl4ZCdUMjItmpJ4T2PFTExp4knoW+00qMBdZddTyTHihQNq0Alaky7JGGoiMXNeAgf0CCLgsxFaw8JhizFw1pmrrXSBGDQF0VwAD9VQQ2YRFotsMzfbOlFlwWlAn6q2plDYXiTRJDPDdK+h5amsYIn0KEDKzISAAZ1V39vMLIsl7FyYig+AFHcROu3iobBShA3NbH40IlzEtbuiJXbVcWbFYIvVtwloAQn3WmoaTmpNg2mInE0cbcpbg+we6uAM4nD6l+ioQ1YZXTDl8TTYjErlO0LLc0TNkCmNDPm3qhQ1EKLYPTymlZ7eMfuiaEdgO8PsGpzWIkdkdE5GHxUgpIR+7gNvdRFMlmjtQ02goB44AJobNAWEPtFKR6OqjMqWYxcm6Tq5eKS1xW8WHK43rnfTk7y0nCcv3Jo/yUNqN7J6/gaGAy4iR6oib2fZjQd+yQTZiKgDYg2ycF2oa04SgbnQpAx2enU3Rf8KyKdKm/VBRd4FLm/KP5VRMVg4DYoFbtJ3KjiaLAf0FhEaKwvXKqhlK1tkmFikkpKZ3MxhxRMgGYMI66TV9OtohUj5GL195Sg/XRtW5Vb60q6f5qFcHg/tRkiMg13Qy0Hf3mKb30oIzSGCJ35TTsqEYri2aI1zork3b/AMqgp+tyxmWd04C/R9KcPqpIiDKjiUgrF47ySxaeosav0uwoBhSxIJEAXygCbah7wpto0Cc0AamDv+5irgAEUm6M4qYBoRO00wSZ9oYF+M1/qZcUwa/5L1WGWCddsxNsTUY8LGe4MHZSr958ZIw0MissMjOActTpuWw5ixgvlqZQAkqMA4z/ACVkMq93EsHmtrI+m9nCbNqkcjEtmBkHJfNZyXP4THmjcIwNNwIeKaDcSg3hbo/H+dka4+TOXNnK2peAaCP9EX81cNIKXH/vTwh/fOfDB/dTp2JHm/8AnxUBwPo/goOFNegJWorLKizEOn/BRvwX5uku+B12p3ho3SmLhH4RONtvWPQmsKIxqHTPUh4ouAg5yLqXukxoHKYK9IeKNoKZCpMHaInfG82/3Pub8HPPR8JGz3as6iI6KUq77EKafuUcn87Vo+7SBOwpdeSo1uQXq0Jb5gseOWr318Go2dFKScAGLNKGQb3tey9LUVvEp0GhZBx8bvfbVIozmbAsdPceyv8AkWDWXJuM4C+UQceH4R1Juo/yqAgAIA0qWF/yV6MGibUuMOTDg9pUC6+JjVXWJlwjrTsGR5R/kmeqNsKVYCrwjX2L8vF8FU7qjbt0sFFWoNcSe0THinSfl4T7FYqDEdrVx6z4q40O2AwdWXxv8O1L/U9G54qWrJpRXTteCoVbEtrhOikW6FLOsuVq+vbMhvjgvFHSMwgSQbxfGKQSt0eUMek80z2GdlLtif2+DMbCgBIpa8H69vK3e6SlBjkqINlgvR+nGwBYCmrXRyNSGLcVI37Bw5/5oE7eNXhTp7VhRstxOg/nWrpfoxstW8L+9KfwTsgVe83tP4qNkrjGtBpLL5rKQwtM6GBLY80Xq1WUkfxKNaKQp5keK0XsLT+cHgPg2S9hfYTf8JeoKowTs5stYBJwLgv6SdaIxNmDg+Dx9gTfd46Xf4SMSiF2T3z0bfBSdd7OOW+IzUWR/d1EcUKyRf0ZJR4oTK7J/wCU2rrGGlyH9v610ZddgJajvFrogoHufh+tnFJkHKKM5ARzBB+P03//2gAMAwEAAgADAAAAEJJJJJIZJJJJJJJJJJJJJOpJJJJJJJJLrTydpIaaYoJO574KiYhE7NpJbHr/AL6x1+jkf6I7FkUABG3diRJMVLQUOs1ul80vfVwaFj5l8ULxlaW/UzKKIddxyiwKSQ3AwTyBmN4uFy77kG3jlQBaeSSLSSSSRySSSSSSSSSSSSSAySSSSSSSSSSSSSXSSSSSSSSSST//xAAoEQEAAQMEAgICAwEBAQAAAAABEQAhMUFRYXGBkaHwELHB0eFAMPH/2gAIAQMBAT8Q/wDNtJEHjPy/9sFZ07aWdll++P8AjKeXA8022IsrKwQXGrzxTodogq4EKTgTJuU0dgUxlOAwRDdc5rNqJA7FswOZ5gGGgKQgzmAvMN48U0aQiNGFMhPkX4ocqwGTN28aaQ+aRZLK2VscGr4MyZ4i07IJ8NQ9i54J/X7pgLEEYN7uCVvTecgs8rMsdalScWhCGCbyYsc0D3bLW0SPsvrsU7HVBk+YY9UTqCwzgFvMLITjFPGs7keCFtqxi+fxJlFgybz8Eeat9RUEik3Rmbd0aUGs3PNikuJLCABPwZ9KuAGBZu1Laa8M6VJRtEnuP0o6QSMICy3SzobNy0pyDIML5hj01LRiBfhiZhrOmlE4ig2zzEonupPdFBJRsYJFIwPFSTIcpxh8w2oVJgtDAbRlDkTbNGJ1SWG1yBZZ3BzRUXNyIiLRGSIjekbStDAGANA2pix9N6+KlIYmoeNwRgDliBd7ijQIVsYRl3UKt3loHCAq8ij6IoOgIdTKeladBB+w6BHunDyRBarrIrjdpegacQRmaOSkxMWm1J6+wy3yruwcHtcnSDyxT21A6ov8E7/MvEpnou+wjtqUN3L7P3Hp/AgVny3Psgj2VdSK09Mfp1HNSAwpcm/m6fDrRJ7XgwEcMT5o4pQA5bFB5YxqS6RNinLYz7qZAQAkOxqLKWREze1RKIoWvKS+bO3mjCQzKCyGCdVB1dpA0tERpdwCU+ZuukRBq2umgdgIO1alZHkxbUHlg6mnP0SkHSwfK+Ck4ot9Dlfq4BaLC5KCoCqRI0A0M4BVbQYxNslkZzTwESQTAC/Vvs0NkgkGuP5fVM5CEIZuWIzMC29qG5FVJGWatpnkXWASPVSsApboNIRApcJkMhJTlYLFrKsHpX1TyWRMN3LuAPM0bRrEgHExLg8oXqH9BzWxO7dfWKSKblubPAns3p2QiNkxd9qxxFTlbQL+qFLlAuTWMbaLkbYp2IWf7HhLNQlJS3IrfbDo52fwxiK5GFwJxMs+Gk5hKgYXB5L+6nITbCXCIVENb6+KMsXSG9gdi/dmCYpIDGFnxe29WUnKf2NLTAbrHstUuPgrY+7zSiPv91a0tBJ6EKEMJcEyfLStYZhEneAXpxFJJrQUICijErkTL+qj+n80Kmi0pMeZqYbaCei1NnFy/wDxUFFB9f4p+3fqrYxdMzKd51c0/RP1R0QZhRO8I3qeJHN0jEwE0nWRCKCORNq+8/5UlMGbl28RE80Y308UpABAAgGANCj61+6m+h81PMsSpXyu2KMq7UL4trSJq1Ai8bBGs4o27dQp7xSidC4oli+BzOC7ah8xJHaT2dgo0FomLA5C5ReUFBkY2JF8USkHVQxN1YP7qOeCS6dWRp7JVV6MTj9ZKsx4UGZEw5h72agLe5HsEoclYGBUOQLbPNCU64CY3vFSkRTAEb3Y5xzQP+3+6B/0/wB1yakh3e8cxFbXhJEuwpF6aMnTiwj0ln+NawMCBSLgJy8FMe5Am0wbuwXq3XZBAUEyZR/tYc1QSLGxl8VPjkaINF4zF84rcHEELxCmG2M0DBlOB5c+JjWk0dQIV2Fh6aQsboDPA3ewT8cSj7IobV0DYJXeAu1me0PQfIr92wT8v1Ow/wAHPE0AwgcAfy5fUsVCdb8P05qSGx/b9qOKUAOWxVsMYGoJ9j6Rlot8p4yTrLudIpw5Oki5yJ9X/EMbuHWSdrFIzbGHVlu8+aSqZgbLd7g9UNoCANJz8i+Yo1MLa4ItI3nG2cpFxgQBhSzy44xlmp6Mkm5Js9zntKn3UekaCHuD5/womrPdSnRDBu2tZq0ZsfkfzS2ELc2eDp2CRET8BOABswQjHKrlhcmptKNbHdrh2FT8pbOclrtWJV5/HER7IouMpd3gk7mng0Qa5XHolJMyk3KvUGXnyfghahfdHtHVKqXNQ8lSnVCzubnVSvvmTe6eP7rTEw2kdCc5LRImlHmZduXxEcNIgJWgd8NHVPt4fgN5UtALDtBLxMNavDHRY6nKbtBkm14EeyPxFi1l7DPvBy01UnEbLK94PLp+JeKlsEPkkhy1IuIbgsd6vPEU8OZOiA7DxtmloueA0DgLf7QK0iqMgd8oeaevMjkThukm2YmiAy4JM6TdnVG21CCEoN5Se2L75yv4FSNUJjKP8Ur0vL/RwFjYrHiBsJMLuE41p6pRVcq5aOsYJitokpvzxFMmyRtFt95kpeN3arxOLMxr2jShuRxJLou39REaRS8rEGAreCbLTWrEGICxDu8I3GONWi2hcxmDQm0wQTTO5oxWiLSlG+fFAHwRtG5J+7QIkMLj4gW0c81gh1P7PlePwgw5CxO5sxbbcYKTXD+gS0+xSxSyzO7ywexjSKXBcK3X8QEiCngHRlzKG34D0ESTMwifWDSXf8RNI4kS9NPYli1WkdfuACec0sl6bRNmAF95nxWp2T9+DT/yBZxf5IA9i/8AaAVgptac/fn/ALXEWzQjiCP+b//EACgRAQACAQIEBgMBAQAAAAAAAAEAESExQRBhcYFRkaGx0eFAwfAw8f/aAAgBAgEBPxD/ADd2teRDyKfm2mar6GXz06sJasoOxn1v8MG0CXae5GTanrKTVF3TN5kwe0oaLdq4H6W5GJxEikO6hfSxfVHzAmleLda9ouLDtMkidPuEXFJAplTGBR7Qmbu3AoyqFgDmtIRqvtUcNpy4RijKmLLShqWTGszWPaJBR5fcaKI8pf8Ajg24x6TGlQXo8MHrPp/zgAnbsTJM2ywcC00HXT1i0J6ipbvZKD45ZWt9PmHYzx8faAXNrCZbRL3XjdRcvbh0Nl/U8JH3gstTX+5ynHWKBbLYV5uCANvOokIyTlTV3gNq4PHmqUa2IL05cQLLC7UC1l9IkBT1jUK0C+TcpHFieC16p3ZonSEGxMm4ywrbIJS6wpTrDdbfCJDVcwWg84Q5y5gNqpdPL7QU3pfG+vTXgbjVxACvKazgVNgGhAqhMjh5aBiZQX85IzCO57KbrOoe7KtF2HuxOvmn9yzamB2/K+CX7UxM2IULVoYMbojloFZsGiOlerB/6+0xAZX22fzxhonmQHn3xhtQ7SHKQdoCd0iuWpK/IGQ808ktds1qB5iNNXWiLFsj4B+467zXxFoEKCYHOvfWI1Y7QKA+UVU3fSWFHylwJys9oBhOaHqxm/V7wYltP9bBijkOh7Ep0gBtaDS1cVi1acxi1Cgq5Bq9N9ZWsvU+CaCYAF9gvmXqE/zeI0p/HhNQ9BF+yPL6JTul1+GEX/O5wBaDqfuXKe4WFvRrBe03xBFCLWy+BGpaB0+JYi3gc3KCfgEBNdzDpZXSapq6z3CVHPMUC2eMC32m9fHR2HO0HJM/HCwHaVKEO5EFdsRr8+He1eHI912uGw8YszlEDtlv6I9HSP8AAt+uDEb1mjR4LunGBH7B+oYGrtlH7eHmn04JU6qppkgXnYAc8+Qw1obfEWQavocNc7vpw3lrEqpytx93gKbto1/Q4Ia25JZLV1hVquIB7UwDWG2Lhs+kqLs8NTVDDQIGZ0gAUQeqO33NdHp9ysF5hJ5HL7gqeC90xd7jsxmRYQNrdIZnaFNAeX3PaB+5gMPIqaQzwz7r4waqPb7lf1IQAUcBd34Enc8Kg0EweDwZjsHgQ0GP8hFKIHRV9ChdDl+bqMrR3mwHdyC+lHU/NA2/8m5r/CvVbfxv/8QAJRABAQACAgIBAwUBAAAAAAAAAREAITFBUWFxEIGRMECxwfCh/9oACAEBAAE/EP0+G7caGKMRB7E8fvRXQilFoHsoG+lzkHxUtEn5Z9/2esDGVCsDteA7UMfhnI0IEQa8ikZcg55VbXUFVUUqGNdWiRFbm2A8d4J7hwNZwhdPC1QGIu1C2NzOHkMGuFEqmLSc3QWwKnBcJ5kw8nNuCH54R3/ARdho7WgMB0IvhZSsK45jeCAjPTh5xQPaINt1kQOqOc3saYSBbjs2kPQIdAPJywYtkt3jvbtx9kdgtlUhtuDXEHU0qnjUNIo7kWW+zHyDKhBdj8ZOYFijkJQ05mjqRypPRry0CSs3wjrdCYl9KdBJcisB3hnRumjY7S90+MNarML1DFmeVU2l1xlzSirYta8hEbJyjxFsNjrd1vXiVCbaEzDo9WqAIvbt2uQ411H9YtcKpMvmiV6DDCe6BC3oLFjeDkydI26h4oTvmAOaMUQeN6FnVwqyN6+gFCAaWAPQV0NwpoDbLRPTjojl1B0yidBN8THFzlKOH1dq/GgDEWznBwdYDokwgVOSiU+MvbTb2rElWiA80ID6vZV08kMAAMLMSnl/Srfir+zkP1ZcEz1Ou6KPO1Wb8RpYeifPH/aIZ0q1LHfjSYecB4Iwj2qBgxIsZ8midpZya1O4QEtmxYqUHtkPbixRkAA9LYnhfXR9Agjcg8wF6T1m/wBHaHOD3Ap1bx9NrRkS9jhT4BXjAgWQsepEKF34tGNs3IjdoBRRCCCqxtFxki+/C/jj51Xg8h8ALlA9wfpKqDiNPbpR1UUc1NRyuKjdNsMKOnMNIChnUyFC8z0FaOvKQrgCP2QvVTv04Goh00h8dqqJSwrKeqMr0XcVS5YIqgnWA2tbihecckUWcFWkBJzDrFOyaPISqurwcqAubF6jxWAVgVOjbh0X06cAqKgI1dbxYuWlAtqJLOwtIODGMiS2kiQvJ9hwXYHo2obt5o5BgnkjFDyhVed41lQIdVugPPWs4TG6K2BO2yQRQwoE7negOruOR8MEnIikpQtSk8C7yaRhWZBUbt9AwiYe2imlF03EqKrhBEDbhcGHI/L4xJSPMCQaSoeF8MIiyoIcxReuPOHBGNMdwaVsgLIq1X09l6wD2MeLricE/cbmk8MAAAA0B1h+ctj2hu0vZwRmGgB1PKy+XjYH0iGCtK0aE6CuQFHMBrDSgiKDbsw+Py5csGDEBQYhxiAp55f6OfdcExIVNOHc/Lmga8Yk92ht33wQlIsV0jgD1xRQIDYgaN5vJtKe0aSljgIDCXKhSlJOKvnB45uQiXCi78Y4jY/D8g1LKAM/zGfQ5rYTu8KhWAX0ZMLBCwhWGABvrDGxAGIASAEAxMWrVNj+WFd/b398H4noX8jKAe4Fxyo01OJhTqOv5zDAHK5IrAqUjqh4wbQiaY5DaLOYeMd0otSgUBBE2OcJ87YgNkXZsamzVm3zk3cf5jLUwHtLVUqqrVcjf7D056CgD/6MN6DM3WBQgppa4ap+XigSFUY94i8h3xKhAAEItDhwQIzKBC4FEQm6Zw+XN25NwwhugqOM58KW77ILtgN5H6cSCsELEI7pNGOJ2u80LPe0ILgZ55AptXlrDQL1jxIERkKAAtaOsfFEhfqnmtkohi7wuNBWaoEG9MKY8ULVycjS6XmZKvuFZ5QCJJybzQZBPY1CMRnMTzjYBElpAg61CX4YB9xP/DjN/wBEbMbtx8RbqoVVRNmHlkhbKk6BVCd4gjAclbAKmhSMYpjwwH2OpgB4O/DjbRTXQUCGiwDtzcgmKIARU1fyYJJifViHTy1AAvWR6MRREBSHTazc2ZGQNLgN6oINO5xgRYowS/INlFBljBjtj8lTlFVdZksHVG5BW2FBqE2fQJO6XnNa1wJnYLymPncK5LwR8dx7Q1d9NRQ16ZXDkNp240IbQZXVILvgYcqEDlgrGL48s19nrc94lYFS8hfcFfY5xpjUaR8AuJvZgaC9AOjYOjc3FIZpBdCnWhFtmYyIlvSEA9b8fSxZSv1E6Qj1iMZNu8PImn1wh71SoHlUcbjXJmA9o8tVStc12zFSAOClO01AGDAmrbEF2ARHKm3QlMQfR4oPhovBQ2GGRBzy/wBplsxN1Yb9j8jhyB5JU++KXgCWYHsf4PU/9xEwWoPmggFnhYRFGVlWwBBZEmKJRkFvgk0l119LIq4BFGlPEDo1wk+hVeE+h/q/OBAU2pDg7fAnJtEH6POm1pQMHULk06DzEwZioLCoXus+Vw/4g4A4A6MVxtvwKHZD6MBgKkQTbstjppGnsdV3nihbK6JGTRROgO/pYxpfUxyCHYAqr0GA2TnjQy72HFT6CZUEjg74GmmE01IsEOkCHoovYeOMEnbBNSjwh6XAAAQOAxjPDOhRh2bvjTLUwh7wTsDe60uIHh9AXkVpQ0pQEKaWznLZqYZR0CjoGipVrKYhiPzc5N2MZGGJ2W9v3wr0WEAMVf8Am8oNDrkMFs+554k1Vty4x2Nj0Q79AcIWdpjvXVoTHko5bBBoAwVdJpkUJ0u+TIlJ1am1AKhTtT3k0ZpAtOmhcpzGTJyl8MAOgAA9Y+b+KtDF7obI1vEhkjBUYI0PtDyWKO5vNg4hzuzmmvLFlTMpA0OZCqKAEhiUgYDgYEU8dogEYudURixCCV5B5GfnSsuA7MKzS406a5sIZokbm020EO1F0BIRrUTjw5cEUENIsaaeCaTIDXc6HkIBTSWTd+lNUxA7oCIa0YjditDYoBcaiB3y9q1x5ooqXo6CxqoNsIH00BwBo+moD+IkrW1qCSB3wyw9SS8wV27Lvg7YwOCJoIc0CN1tvxCn0bLVWN7aQh8uWtYj1BSgQdJQdIObpKXQKqqqqqqqqqrX9JZRWKOjgWm9T7/e+1P0sn4HFsR8rRqeqB7Pkf3ii/bsDie4uLSSmgNntg/bf//Z">
          </td>
        </tr>
      </table>

      <table class="borderless">
        <tr>
          <td>Order #{{ $order_number }}</td>
          <td class="text-right">{{ $delivery_date }}</td>
        </tr>
      </table>

      <table class="borderless">
        <tr>
          <th class="text-right" rowspan="3" style="width:50%">Ship To</th>
          <td class="text-right">{{ $customer_name }}</td>
        </tr>
        <tr>
          <td class="text-right">{{ $customer_address }}</td>
        </tr>
        <tr>
          <td class="text-right">{{ $customer_city }}</td>
        </tr>
        <tr>
          <th class="text-right" style="width:50%">Driver</th>
          <td class="text-right">{{ $employee_id }}</td>
        </tr>
      </table>

      <table>
        <thead>
          <tr>
            <th class="text-left">Item #</th>
            <th class="text-left">Description</th>
            <th class="text-left">Scan Time</th>
            <th class="text-left">Location</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $item)
            <tr>
              <td class="text-left">{{ $item['boxnumber'] > 100 ? $item['boxnumber'] : $item['linenumber'] }}</td>
              <td class="text-left">{{ $item['itemdescription'] }}</td>
              <td class="text-left">{{ date('Y-m-d h:i:s A', strtotime($item['scantime'])) }}</td>
              <td class="text-left">{{ round($location[0], 2) }}, {{ round($location[1], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <th class="text-left" colspan="3">Total Delivered</th>
          <td class="text-left">{{ count($items) }} / {{ $total_items }}</td>
        </tfoot>
      </table>

      @if($photos && count($photos) > 0)
        <table id="photos" class="borderless">
          <tr>
            @foreach ($photos as $photo)
              <td><img src="{{ $photo }}"></td>
            @endforeach
          </tr>
        </table>
      @endif

      @if($comments)
        <table>
          <thead>
            <tr>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-left">{{ $comments }}</td>
            </tr>
          </tbody>
        </table>
      @endif

      <table class="signature">
        <tr>
          <th class="text-right">Received By</th>
          <td>{{ $receiver_name }}</td>
          <td style="width:45%">
            <img id="signature" src="{{ $signature }}">
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>
