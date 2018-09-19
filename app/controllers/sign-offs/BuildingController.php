<?php namespace SignOffs\Controller;

class BuildingController extends \BaseController {

    public function find($projectId, $buildingId)
    {
        $data = \WMBuildingInfo
            ::where('ProjectNumber', '=', $projectId)
            ->where('BuildingNumber', '=', $buildingId)
            ->remember(2)
            ->first();

        return \Response::prettyjson(array(
            'data' => $data
        ));
    }

    public function get($project_id)
    {
        $sql = "CALL installation_floorsignoffs.GetBuildingSummary(?)";

        try {
            $connection_string = 'mysql:host=archdb.starlinewindows.com;port=3306;dbname=installation_floorsignoffs';
            $dbh = new \PDO($connection_string, 'floorsignoffs', 'bw8dnak+9', array( \PDO::ATTR_PERSISTENT => false));
            $stmt = $dbh->prepare($sql);

            // call the stored procedure
            $stmt->execute(array($project_id));
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

    public function getPhoto($project_id, $building_id)
    {
        $src = storage_path() . "/signoffs/photos/{$project_id}/{$building_id}";
        if (!file_exists($src))
        {
            return null;
        }

        $files = scandir($src, \SCANDIR_SORT_DESCENDING);
        if (count($files) < 3) // ignore . and ..
        {
            return null;
        }

        $tmp = explode(',', $files[0]);
        $ext = end($tmp);

        return \Response::make(file_get_contents("{$src}/{$files[0]}"), 200, [
            'Content-Type'              => "image/$ext",
            'Content-Disposition'       => "filename={$project_id}-{$building_id}-{$files[0]}",
            'Content-Transfer-Encoding' => 'binary',
            'Accept-Ranges'             => 'bytes'
        ]);
    }

    public function uploadPhoto($project_id, $building_id)
    {
        $data = \Input::get('base64Image');
        $data = explode(',', $data);
        $meta = explode(';', $data[0]);
        $mime = str_replace('data:', '', $meta[0]);
        $data = $data[1];

        $dest = storage_path() . "/signoffs/photos/{$project_id}/{$building_id}/";
        if (!file_exists($dest))
        {
            $mask = umask(0);
            mkdir($dest, 0774, true);
            umask($mask);
        }

        $date = new \DateTimeImmutable();
        $filename = $dest . $date->getTimestamp();
        switch ($mime) {
            case 'image/png':
                $filename .= '.png';
                break;
            case 'image/jpg':
                $filename .= '.jpg';
                break;
            default:
                // assume jpg?
                $filename .= '.jpg';
                break;
        }

        $file = fopen($filename, 'wb');
        if ($file === false)
        {
            return \Response::prettyjson(['error' => 'failed to create file'], 500);
        }
        fwrite($file, base64_decode($data));
        fclose($file);

        return \Response::prettyjson(['message' => 'photo saved']);
    }
}
