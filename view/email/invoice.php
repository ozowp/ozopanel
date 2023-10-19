<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WP Access Manager Email</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;900&display=swap" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;900&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
    }

    .wam {
      margin: 0;
      padding: 40px 0 60px;
      background-color: aliceblue;
      -webkit-font-smoothing: antialiased;
      -webkit-text-size-adjust: none;
      width: 100% !important;
      height: 100%;
    }

    .wam-container {
      display: block !important;
      border-collapse: collapse;
      max-width: 559px;
      margin: 0 auto;
      margin-top: 50px;
    }

    .wam-table-container {
      display: block !important;
      padding: 30px 30px 60px 40px;
      border-spacing: 0;
      border-collapse: collapse;
      border-top: 10px solid #4c6fff;
      border-radius: 17px;
      max-width: 559px;
      background-color: #fff;
      margin: 0 auto;
      margin-bottom: 20px;
    }

    .wam-head {
      min-width: 490px;
    }

    .wam-logo {
      width: 200px;
    }
    .wam-logo img{
      max-width: 200px !important;
      max-height: 90px !important;
    }

    .wam-address {
      width: 202px;
      padding: 13px 13px 13px 20px;
      background-color: #EDF2F7;
      border-radius: 9px;
    }

    .wam-address h6 {
      font-family: 'Inter', sans-serif;
      font-style: normal;
      font-weight: 600;
      font-size: 14px;
      line-height: 150%;
      color: #2D3748;
      margin-bottom: 2px;
    }

    .wam-address p {
      font-family: 'Inter';
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 140%;
      color: #4A5568;
    }

    .wam-msg {
      display: inline-table;
      margin: 35px 0 45px 0;
    }

    .wam-msg p {
      font-family: 'Inter';
      font-style: normal;
      font-weight: 400;
      font-size: 14px;
      line-height: 160%;
      color: #1A202C;
      display: inline-block;
    }

    .wam-invoice-table {
      width: 100%;
      /* border-collapse: separate; */
      /* border-spacing: 16px; */
      border-collapse: collapse;
      margin-bottom: 40px;
    }

    .wam-invoice-table tr th {
      text-align: left;
      padding: 8px;
      font-family: 'Inter';
      font-style: normal;
      font-weight: 600;
      font-size: 16px;
      line-height: 150%;
      color: #2D3748;
    }

    .wam-invoice-table tr td {
      padding: 8px;
      text-align: right;
      font-family: 'Inter';
      font-style: normal;
      font-weight: 400;
      font-size: 16px;
      line-height: 150%;
      color: #718096;
    }

    .wam-total {
      background-color: #EDF2F7;
      margin-left: 10px !important;
    }

    .wam-total th {
      font-weight: 900 !important;
      background-color: #EDF2F7;
      color: #1A202C !important;
    }

    .wam-total td {
      font-weight: 900 !important;
      padding-right: 10px !important;
      background-color: #EDF2F7;
      color: #1A202C !important;
    }

    .wam-thanks {
      font-size: 14px;
      color: #1A202C;
      padding-bottom: 45px;
    }

    .wam-regards {
      margin-top: 13px;
      font-weight: 600;
    }

    .wam-view {
      text-align: center;
    }

    .wam-view p {
      margin-top: 29px;
      color: #1A202C;
      font-size: 14px;
      font-weight: 400;
      line-height: 160%;
    }

    .wam-btn {
      font-family: 'Inter';
      font-style: normal;
      font-weight: 600;
      font-size: 14px;
      line-height: 14px;
      padding: 16px 38px;
      text-decoration: none;
      color: #fff !important;
      background-color: #4C6FFF;
      border: 1px solid #4C6FFF;
      border-radius: 8px;
      margin-bottom: 160px;
    }

    .wam-footer-container {
      overflow: hidden;
      border-spacing: 0;
      text-align: center;
      width: 559px;
      margin: 0 auto;
    }

    .wam-footer-text h3 {
      font-style: normal;
      font-weight: 700;
      font-size: 20px;
      line-height: 140%;
      text-align: center;
      color: #000000;
    }

    .wam-footer-text p {
      font-family: 'Inter';
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 140%;
      text-align: center;
      color: #000000;
    }

    .wam-social {
      margin-top: 16px;
    }
    
    .wam-social li {
      display: inline-block;
    }

    .wam-social li a {
      display: inline-block;
      margin: 0 3px;
      list-style: none;
      padding: 7px;
      width: 30px;
      height: 30px;
      background-color: #fff;
      border-radius: 100%;
      line-height: 16px;
      text-align: center;
      vertical-align: middle;
    }

    .wam-social li a img {
      max-height: 16px !important;
      max-width: 16px !important;
    }
  </style>
</head>

<body class="wam">
  <table class="wam-container">
    <tbody>
      <tr>
        <td>
          <table class="wam-table-container">
            <tbody>
              <tr>
                <td>
                  <table class="wam-head">
                    <tr>
                      <td class="wam-logo">
                        {org_img}
                      </td>
                      <td class="wam-address">
                        <h6>{org_name}</h6>
                        <p>
                          {org_address}
                        </p>
                      </td>
                    </tr>
                  </table>
                </td>

              </tr>
              <tr>
                <td class="wam-msg">
                  <p>{msg}</p>
                </td>
              </tr>
              <tr>
                <td class="wam-view">
                  <a href="{url}" target="_blank" class="wam-btn">{view_txt} {title}</a>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <footer>
            <table class="wam-footer-container">
              <tbody>
                <tr>
                  <td class="wam-footer-text">
                    {footer_text}
                    <ul class="wam-social">
                      {social}
                    </ul>
                  </td>
                </tr>
              </tbody>
            </table>
          </footer>
        </td>
      </tr>
    </tbody>
  </table>
</body>

</html>