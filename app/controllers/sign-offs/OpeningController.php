<?php namespace SignOffs\Controller;

use BaseController, Input, PDO, PDOException, Response;

class OpeningController extends BaseController {

  public function holdOpenings()
  {
    $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
    $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
    $stmt = $dbh->prepare('INSERT IGNORE INTO windowsheld (IWAFId, WindowIndex) VALUES (?, ?)');

    try
    {
      $openings = Input::get('openings');
      if (! (is_array($openings) && count($openings) > 0))
      {
        return Response::prettyjson(['success' => false, 'error' => 'Bad parameters'], 422);
      }

      foreach ($openings as $opening)
      {
        $stmt->execute([$opening['ref_id'], $opening['window_index']]);
      }
      return Response::prettyjson(['success' => true]);
    }
    catch (Exception $ex)
    {
      return Response::prettyjson(['success' => false, 'error' => $ex->message], 500);
    }
  }

  public function releaseOpenings()
  {
    $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
    $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
    $stmt = $dbh->prepare('DELETE FROM windowsheld WHERE IWAFId = ? AND WindowIndex = ?');

    try
    {
      $openings = Input::get('openings');
      if (! (is_array($openings) && count($openings) > 0))
      {
        return Response::prettyjson(['success' => false, 'error' => 'Bad parameters'], 422);
      }

      foreach ($openings as $opening)
      {
        $stmt->execute([$opening['ref_id'], $opening['window_index']]);
      }
      return Response::prettyjson(['success' => true]);
    }
    catch (Exception $ex)
    {
      return Response::prettyjson(['success' => false, 'error' => $ex->message], 500);
    }
  }

  public function saveComments()
  {
      $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
      $dbh = new PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [PDO::ATTR_PERSISTENT => false]);
      $stmt = $dbh->prepare('REPLACE INTO notes (IWAFId, WindowIndex, Notes) VALUES (?, ?, ?)');

      try
      {
        $openings = Input::get('openings');
        if (! (is_array($openings) && count($openings) > 0))
        {
          return Response::prettyjson(['success' => false, 'error' => 'Bad parameters'], 422);
        }

        $comments = Input::get('comments');
        if (! $comments)
        {
            $comments = null;
        }

        foreach ($openings as $opening)
        {
          $stmt->execute([$opening['ref_id'], $opening['window_index'], $comments]);
        }

        return Response::prettyjson(['success' => true]);
      }
      catch (Exception $ex)
      {
        return Response::prettyjson(['success' => false, 'error' => $ex->message], 500);
      }
  }
}
