<?php namespace SignOffs\Controller;

use BaseController, Input, PDO, PDOException, Response;

class SignoffController extends BaseController {

    public function getSignoffs($window_id, $window_index)
    {
        try
        {
            $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
            $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
            $stmt = $dbh->prepare('CALL GetSignoffs(?, ?)');

            // call the stored procedure
            $stmt->execute([$window_id, $window_index]);
            $data = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');

            return Response::prettyjson([
                'success' => true,
                'count' => count($data),
                'data' => $data
            ]);

        }
        catch (PDOException $e)
        {
            return Response::prettyjson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getInspections()
    {
      $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
      $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
      $stmt = $dbh->prepare('CALL getInspections()');
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass');

      $ret = [];
      foreach ($data as $row)
      {
        $key = "{$row->ReportId}.{$row->CategoryId}";

        if (! array_key_exists($key, $ret))
        {
          $ret[$key] = [
            'ReportShortName' => $row->ReportShortName,
            'ReportLongName' => $row->ReportLongName,
            'CategoryName' => $row->CategoryName,
            'Inspections' => []
          ];
        }

        $ret[$key]['Inspections'][$row->InspectionId] = [
          'ReportId' => $row->ReportId,
          'CategoryId' => $row->CategoryId,
          'InspectionId' => $row->InspectionId,
          'InspectionName' => $row->InspectionName
        ];
      }

      return Response::prettyjson($ret);
    }

    public function save() {
      $technician_id = Input::get('technician');
      $openings = Input::get('openings');
      $signoffs = Input::get('signoffs');

      if (! ($technician_id && $openings && $signoffs))
      {
        return Response::prettyjson(['error' => 'Bad parameters'], 422);
      }

      $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
      $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
      $stmt = $dbh->prepare('
        INSERT INTO signoffs (
          IWAFId, WindowIndex, TechnicianId, ReportId, CategoryId, Inspections, ReportDate
        )
        VALUES (
          ?, ?, ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
          TechnicianId = VALUES(TechnicianId),
          Inspections = VALUES(Inspections),
          ReportDate = VALUES(ReportDate)
      ');

      try
      {
        foreach ($openings as $opening)
        {
          foreach ($signoffs as $signoff)
          {
            $stmt->execute([
              $opening['ref_id'],
              $opening['window_index'],
              $technician_id,
              $signoff['report_id'],
              $signoff['category_id'],
              $signoff['inspections'],
              date('Y-m-d') . ' 00:00:00'
            ]);
          }
        }

        return Response::prettyjson(['success' => true]);
      }
      catch(Exception $ex)
      {
        return Response::prettyjson(['success' => false, 'error' => $ex->message], 500);
      }
    }

}
