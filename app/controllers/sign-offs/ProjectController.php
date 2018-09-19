<?php namespace SignOffs\Controller;

use SignOffs\Model\Projects as Projects;
use SignOffs\Model\ProjectsHeld as ProjectsHeld;

class ProjectController extends \BaseController {

    public function find($project_id)
    {
        $res = Projects::find($project_id);
        $is_held = ProjectsHeld::find($project_id);
        $res->is_held = (isset($is_held) && !is_null($is_held));

        return \Response::prettyjson(array(
            'data' => $res
        ));
    }

    public function toggleHold($project_id)
    {
      $is_held = ProjectsHeld::find($project_id);
      if (isset($is_held) && !is_null($is_held))
      {
        $is_held->delete();
        return \Response::json(false);
      }
      else
      {
        $is_held = new ProjectsHeld();
        $is_held->project_id = $project_id;
        $is_held->save();
        return \Response::json(true);
      }
    }

    public function recent($technician_id)
    {
      try
      {
          $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
          $dbh = new \PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', [\PDO::ATTR_PERSISTENT => false]);
          $stmt = $dbh->prepare('CALL getRecentProjects(?)');

          // call the stored procedure
          $stmt->execute([$technician_id]);
          $data = $stmt->fetchAll(\PDO::FETCH_CLASS, 'stdClass');

          return \Response::prettyjson([
              'success' => true,
              'count' => count($data),
              'data' => $data
          ]);
      }
      catch (\PDOException $e)
      {
          return \Response::prettyjson([
              'success' => false,
              'error' => $e->getMessage()
          ]);
      }
    }

    public function search($term)
    {
        if (! isset($term) || strlen($term) < 1)
        {
            return \Response::prettyjson(array('error' => 'Missing search term.'));
        }

        $regex = \DB::getPdo()->quote("^[^[:alnum:]]*{$term}.*");

        $res = Projects
            ::whereRaw("ProjectNumber NOT LIKE '%.D%' AND (ProjectNumber REGEXP {$regex} OR ProjectName REGEXP {$regex})")
            ->orderBy('ProjectName')
            ->get(array('ProjectNumber', 'ProjectName'));

        return \Response::prettyjson(array(
            'data' => $res,
            'total' => count($res)
        ));
    }

    public function show()
    {
      $start = \Input::get('start', 0);
      $limit = \Input::get('limit', 50);
      $search = \Input::get('search', null);
      $technician_id = \Input::get('technician', null);

      $conditions = "ProjectNumber NOT LIKE '%.D%'";

      if ($search !== null && strlen($search) > 0)
      {
          $conditions .= "
              AND (
                  ProjectNumber LIKE '%$search%' OR
                  ProjectName LIKE '%$search%'
              )
          ";
      }

      if ($technician_id == 9)
      {
          $conditions .= "    AND (BillingCountry = 'USA' OR ShipCountry = 'USA')";
      }
      else if ($technician_id != 3)
      {
          $conditions .= "    AND (BillingCountry != 'USA' AND ShipCountry != 'USA')";
      }

      $total = Projects::whereRaw($conditions)->remember(2)->count();

      $data = Projects
          ::whereRaw($conditions)
          ->orderBy('ProjectName')
          ->skip($start)
          ->take($limit)
          ->remember(2)
          ->get(array('ProjectNumber', 'ProjectName'));

      return \Response::prettyjson(array(
          'data' => $data,
          'total' => $total
      ));
    }

    public function showGroup($group_name)
    {

        if (strlen($group_name) < 1)
        {
            return \Response::prettyjson(array('error' => 'Invalid group name: ' . $group_name));
        }

        $start = \Input::get('start', 0);
        $limit = \Input::get('limit', 50);
        $search = \Input::get('search', null);

        $conditions = "ProjectNumber NOT LIKE '%.D%'";

        if ($group_name === '0-9')
        {
            $chars = [];
            for ($i = 0; $i <= 9; ++$i)
            {
                $chars[] = "{$i}";
            }
        }
        else
        {
            $chars = str_split($group_name);
        }

        $statements = array_map(function($char)
        {
            return "ProjectName REGEXP '^[^[:alnum:]]*{$char}'";
        }, $chars);

        $conditions .= ' AND (' . implode($statements, ' OR ') . ')';

        if ($search !== null && strlen($search) > 0)
        {
            $conditions .= "
                AND (
                    ProjectNumber LIKE '%$search%' OR
                    ProjectName LIKE '%$search%'
                )
            ";
        }

        $total = Projects::whereRaw($conditions)->remember(2)->count();

        $data = Projects
            ::whereRaw($conditions)
            ->orderBy('ProjectName')
            ->skip($start)
            ->take($limit)
            ->remember(2)
            ->get(array('ProjectNumber', 'ProjectName'));

        return \Response::prettyjson(array(
            'data' => $data,
            'total' => $total
        ));

    }
}
