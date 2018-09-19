<?php namespace Workorders;

class MaterialRemnantsController extends \BaseController {
  public function confirm($barcode)
  {
    $cart_id = \Input::get('cart_id', null);
    $length = \Input::get('length', 0);

    if ($barcode[0] !== 'M' && $barcode[1] !== 'R')
    {
      $barcode = "MR{$barcode}";
    }

    $valid = true;
    if (! ($cart_id && strlen($cart_id) > 0))
    {
      $valid = false;
    }

    if ($valid)
    {
      \DB::connection('archdb-wm')->update('CALL workorders.confirmRemnant(?, ?)', [$barcode, $cart_id]);
      return $this->get($barcode);
    }
    return \Response::prettyjson(['error' => 'invalid input'], 400);
  }

  public function delete($barcode)
  {
    return \DB::connection('archdb-wm')->update('CALL workorders.removeRemnant(?)', [$barcode]);
  }

  public function get($barcode)
  {
    if ($barcode[0] === 'M' && $barcode[1] === 'R')
    {
      $barcode = substr($barcode, 2);
    }

    $sql = '
      SELECT *
      FROM workorders.material_remnants
      WHERE id = ?
    ';

    $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), [$barcode]);
    if ($data && count($data) > 0)
    {
        return \Response::prettyjson($data[0]);
    }
    return \Response::prettyjson(null);
  }

  public function getCart($cart_id)
  {
    $sql = '
      SELECT *
      FROM workorders.material_remnants
      WHERE cart_id = ?
    ';

    $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), [$cart_id]);
    return \Response::prettyjson($data);
  }

  public function getLastUsedCart()
  {
    $sql = '
      SELECT cart_id, date_confirmed
      FROM workorders.material_remnants
      WHERE date_confirmed IS NOT NULL
      ORDER BY date_confirmed DESC
      LIMIT 1
    ';

    $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), []);
    if (count($data) > 0)
    {
      return \Response::prettyjson([
        'cart_id' => $data[0]->cart_id
      ]);
    }

    return \Response::prettyjson([
      'cart_id' => 'REM01'
    ]);
  }

  public function listConfirmed()
  {
    $sql = '
      SELECT *
      FROM workorders.material_remnants
      WHERE date_confirmed IS NOT NULL AND date_removed IS NULL
      ORDER BY date_confirmed DESC
    ';

    if (\Input::has('start'))
    {
      $sql .= '
        LIMIT ?, ?
      ';

      $qParams[] = \Input::get('start', 0);
      $qParams[] = \Input::get('count', 10);
    }
    else
    {
      $qParams = [];
    }

    $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), $qParams);
    return \Response::prettyjson($data);
  }

  public function listConfirmedWithUsedBy()
  {
      return $this->listConfirmed();
  }

  public function listUnconfirmed()
  {
      $sql = '
        SELECT *
        FROM workorders.material_remnants
        WHERE date_confirmed IS NULL AND date_removed IS NULL
      ';

      if (\Input::has('start'))
      {
        $sql .= '
          LIMIT ?, ?
        ';

        $qParams[] = \Input::get('start', 0);
        $qParams[] = \Input::get('count', 10);
      }
      else
      {
        $qParams = [];
      }

      $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), $qParams);
      return \Response::prettyjson($data);
  }

  public function listScrapCandidates()
  {
    $data = \DB::connection('archdb-wm')->select(\DB::raw('CALL workorders.findScrapableRemnants()'), []);
    return \Response::prettyjson($data);
  }

  public function resize($barcode)
  {
    $length = \Input::get('length', 0);
    if ($length < 1)
    {
      return \Response::prettyjson(['error' => 'invalid input'], 400);
    }

    if ($barcode[0] === 'M' && $barcode[1] === 'R')
    {
      $barcode = substr($barcode, 2);
    }

    return \DB::connection('archdb-wm')->update('UPDATE workorders.material_remnants SET `length` = ? WHERE id = ? LIMIT 1', [$length, $barcode]);
  }

  public function search()
  {
    $query = \DB::connection('archdb-wm')
      ->table('workorders.material_remnants')
      ->whereNull('date_removed')
      ->where(function($query)
      {
        $type = \Input::get('type', 'all');
        switch ($type)
        {
          case 'confirmed':
            $query->whereNotNull('date_confirmed');
            break;
          case 'unconfirmed':
            $query->whereNull('date_confirmed');
            break;
          default:
            break;
        }

        $search_term = \Input::get('searchTerm', '');
        $search_term = str_replace(['> ', '>= ', '< ', '<= '], ['>', '>=', '<', '<='], $search_term);
        $search_terms = explode(' ', $search_term);

        for ($i = 0; $i < count($search_terms); ++$i)
        {
          $predicate = $this->_parseSearchTerm($search_terms[$i]);

          $query->where(function($query) use ($predicate)
          {
            for ($j = 0; $j < count($predicate[0]); ++$j)
            {
              if ($j === 0)
              {
                $query->where($predicate[0][$j], $predicate[1], $predicate[2]);
              }
              else
              {
                $query->orWhere($predicate[0][$j], $predicate[1], $predicate[2]);
              }
            }
          });
        }
      })
      ->orderBy('reserved')
      ->orderBy('date_confirmed')
      ->orderBy('date_created');

    if (\Input::has('start'))
    {
      $query->skip(\Input::get('start', 0));
      $query->take(\Input::get('count', 10));
    }

    $data = $query->get();
    return \Response::prettyjson($data);
  }

  public function transfer($barcode)
  {
    $cart_id = \Input::get('cart_id', null);
    if (!$cart_id)
    {
      return Response::prettyjson(['error' => 'invalid input'], 400);
    }

    return \DB::connection('archdb-wm')->update('CALL workorders.transferRemnant(?, ?)', [$barcode, $cart_id]);
  }

  private function _getExpectedUsedByDate($profile_code, $ext_colour, $int_colour, $max_length)
  {
      $sql = '
          SELECT MIN(IW.Expected_Delivery_Date) AS used_by
          FROM ice.extrusions AS E
          JOIN ice.windowtag AS W ON W.WindowTagID = E.WindowTagID
          JOIN ice.windowset AS WS ON WS.WindowSetID = W.WindowSetID
          JOIN projects.info_windows_assigned_to_floor AS IW ON IW.Project_ID = WS.ProjectID AND IW.Window_Name = W.WinNumber
          WHERE E.ProfCode = ? AND E.exteriorcolor = ? AND E.interiorcolor = ? AND E.cutlength <= ?
          AND IW.Qty_Required > IW.Qty_Processed AND IW.Expected_Delivery_Date > CURRENT_DATE()
      ';
      $data = \DB::connection('archdb-wm')->select(\DB::raw($sql), [$profile_code, $ext_colour, $int_colour, $max_length]);
      if ($data && count($data) > 0)
      {
          return $data[0]->used_by;
      }
      return null;
  }

  private function _parseSearchTerm($term)
  {
    if (strpos($term, '>') === 0)
    {
      $columns = ['length'];
      $operator = '>';
      $term = substr($term, 1);

      if (strpos($term, '=') === 0)
      {
        $operator .= '=';
        $term = substr($term, 1);
      }
    }
    else if (strpos($term, '<') === 0)
    {
      $columns = ['length'];
      $operator = '>';
      $term = substr($term, 1);

      if (strpos($term, '=') === 0)
      {
        $operator .= '=';
        $term = substr($term, 1);
      }
    }
    else
    {
      $columns = [
          'profile_code',
          'colour_code',
          'list_number',
          'order_numbers',
          'reserved_listnumber',
          'reserved_ordernumbers'
      ];
      $operator = 'like';
      $term = "%${term}%";
    }

    return [$columns, $operator, $term];
  }
}
