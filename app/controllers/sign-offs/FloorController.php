<?php namespace SignOffs\Controller;

class FloorController extends \BaseController {

    public function find($projectId, $buildingId, $floorId)
    {
        $data = \WMFloorInfo
            ::where('ProjectNumber', '=', $projectId)
            ->where('BuildingNumber', '=', $buildingId)
            ->where('FloorNumber', '=', $floorId)
            ->remember(2)
            ->first();

        return \Response::prettyjson(array(
            'data' => $data
        ));
    }

    public function get($project_id, $building_id)
    {
        $sql = "CALL installation_floorsignoffs.GetFloorSummary(?, ?)";

        try {
            $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
            $dbh = new \PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', array( \PDO::ATTR_PERSISTENT => false));
            $stmt = $dbh->prepare($sql);

            // call the stored procedure
            $stmt->execute(array($project_id, $building_id));
            $data = $stmt->fetchAll(\PDO::FETCH_CLASS, 'stdClass');

            return \Response::prettyjson(array(
                'success' => true,
                'count' => count($data),
                'data' => $data
            ));

        } catch (\PDOException $e) {
            return \Response::prettyjson(array(
                'success' => false,
                'error' => $e->getMessage()
            ));
        }
    }

}