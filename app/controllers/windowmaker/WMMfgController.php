<?php namespace WindowMaker;

use Input, Response;

class WMMfgController extends \BaseController {

  public function glassTypes()
  {
      $tableState = json_decode(Input::get('tableState', null));
      if ($tableState !== null)
      {
          $start = isset($tableState->pagination->start)
              ? $tableState->pagination->start
              : 0;

          $number = isset($tableState->pagination->number)
              ? $tableState->pagination->number
              : 10;

          if (isset($tableState->sort->predicate))
          {
              $order_by = $tableState->sort->predicate;
              $order_dir = $tableState->sort->reverse ? 'desc' : 'asc';
          }
          else
          {
              $order_by = 'code';
              $order_dir = 'asc';
          }

          $where = function($query) use ($tableState)
          {
              if (! (isset($tableState->search) && isset($tableState->search->predicateObject)))
              {
                  return;
              }

              foreach ($tableState->search->predicateObject as $key => $value)
              {
                  switch($key)
                  {
                      case "global":
                        // fall through
                      case '*':
                          $query->where('code', 'like', "%{$value}%");
                          $query->orWhere('description', 'like', "%{$value}%");
                          break;

                      default:
                          $query->where($key, 'like', "%{$value}%");
                          break;
                  }
              }
          };
      }
      else
      {
          $order_by = 'code';
          $order_dir = 'asc';
          $start = 0;
          $number = 65535; // Unlimited
          $where = function() {};
      }

      $data = WMGlassTypes::where('active', 1)->where($where)
          ->orderBy($order_by, $order_dir)
          ->skip($start)
          ->take($number)
          ->get(['code', 'description', 'category']);

      return Response::prettyjson($data);
  }

}
