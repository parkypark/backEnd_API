<?php namespace TimeClock\Controller;

use DB;
use Response;

class TimeClockController extends \BaseController {

  public function getEmployee($employee_id)
  {
    $employee = DB::connection('production-vinyl')->select('
      SELECT employeenumber, firstname, lastname, company AS division
      FROM ic.consumableemployee
      WHERE employeenumber = ? AND deleted = 0
    ', [$employee_id]);

    if (! count($employee) > 0)
    {
      $employee = DB::connection('archdb')->select('
        SELECT ED.EmployeeNumber AS employeenumber, EPD.FirstName AS firstname, EPD.LastName AS lastname, ED.CompanyCode as division
        FROM timeclock.EmployeeData AS ED
        JOIN timeclock.EmployeePersonalDetail AS EPD ON EPD.EmployeeNumber = ED.EmployeeNumber
        WHERE ED.EmployeeNumber = ? AND ED.`Status` = "A"
      ', [$employee_id]);
    }

    if (! count($employee) > 0)
    {
      Return Response::prettyjson(['error' => 'Employee does not exist'], 400);
    }

    $employee = $employee[0];
    $employee->lastname = $this->titleCase($employee->lastname);
    $employee->firstname = $this->titleCase($employee->firstname);

    return Response::prettyjson($employee);
  }

  public function getEmployeeName($employee_id)
  {
    try
    {
      $sql = '
        SELECT CONCAT_WS(" ", FirstName, LastName) AS employee_name
        FROM timeclock.EmployeePersonalDetail
        WHERE EmployeeNumber = ?
      ';

      $employees = \DB::connection('archdb')->select($sql, [$employee_id]);

      if (count($employees))
      {
        $employee_name = $this->titleCase($employees[0]->employee_name);
        Return \Response::prettyjson(compact('employee_name'));
      }
      else
      {
        Return \Response::prettyjson(['error' => 'Employee does not exist'], 400);
      }
    }
    catch(Exception $e)
    {
      Return \Response::prettyjson(['error' => $e->getMessage()], 400);
    }
  }

  private function titleCase($input_string)
  {
    $words = explode(' ', $input_string);
    $newWords = [];

    foreach ($words as $word) {
      $newWords[] = ucfirst(strtolower($word));
    }

    return implode(' ', $newWords);
  }

}
