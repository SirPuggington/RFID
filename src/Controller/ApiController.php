<?php

namespace App\Controller;

use Carbon\Carbon;
use Exception;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Pimcore\Controller\FrontendController;

class ApiController extends FrontendController
{
    /**
     * @Route("/rest/send_rfid")
     * @throws Exception
     */
    public function receiveScan(Request $request)
    {
        $file="temp/scan.txt";
        file_put_contents($file,$request->get("data"));
        return $this->json(['success' => true]);
    }



    /**
     * @Route("/rest/get_data")
     * @throws Exception
     */
    public function getScan()
    {
        $file = 'temp/scan.txt';
        $data=file_get_contents($file);
        $uid = explode(";",$data)[0];
        $station=explode(';',$data)[1];
        $employee=DataObject\Employee::getByPath("/Employees/$uid");

        if ($employee){
            $name=$employee->getName();
        }else{
            $name="";
        }



        $last_modified = filemtime($file);
        $now=Carbon::now();

        return $this->json([
            'id' => $uid,
            'now'=>$now,
            'last_modified' => $last_modified,
            'name'=>$name,
            'station'=>$station]);
    }
}
