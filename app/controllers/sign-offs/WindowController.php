<?php namespace SignOffs\Controller;

class WindowController extends \BaseController {

    public function get($project_id, $building_id, $floor_id)
    {
        // Eloquent has failed miserably here, no errors, just no data. At lease PDO always works!
        $window_names = \Input::get('windowNames', null);
        $sql = "CALL installation_floorsignoffs.GetWindowSummary(?, ?, ?, ?)";

        try {
            $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
            $dbh = new \PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', array( \PDO::ATTR_PERSISTENT => false));
            $stmt = $dbh->prepare($sql);

            // call the stored procedure
            $stmt->execute(array($project_id, $building_id, $floor_id, $window_names));
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