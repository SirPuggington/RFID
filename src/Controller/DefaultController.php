<?php

namespace App\Controller;

use Carbon\Carbon;
use Exception;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends FrontendController
{
    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function defaultAction(Request $request): Response
    {
        $stations = DataObject\Station::getList();

        return $this->render('default/selectStation.html.twig',[
            'stations'=>$stations
        ]);
    }

    /**
     * @Route("/station/{id}")
     */
    public function stationAction(Request $request, $id):Response{

        $station=DataObject\Station::getByPath("/Stations/$id");

       return $this->render('default/default.html.twig',[
           'station'=>$station,
       ]);
    }

    /**
     * @Route("/buy/{station}/{user}", methods="POST")
     * @throws Exception
     */
    public function buyItem($user, $station){
        $user= DataObject\Employee::getByPath("/Employees/$user");
        $total=0;
        $numberOfItems=0;
        $products=$_POST;
        $transaction=new DataObject\Transaction();
        $mainTransactionFolder=DataObject\Folder::getByPath('/Transactions');
        $transactionFolder=DataObject\Folder::getByPath('/Transactions/'.$user->getName());
        if(!$mainTransactionFolder){
            $mainTransactionFolder=new DataObject\Folder();
            $mainTransactionFolder->setParent(DataObject\Folder::getById(1));
            $mainTransactionFolder->setKey('Transactions');
            $mainTransactionFolder->save();
        }
        if(!$transactionFolder){
            $transactionFolder=new DataObject\Folder();
            $transactionFolder->setParent(DataObject\Folder::getByPath('/Transactions'));
            $transactionFolder->setKey($user->getName());
            $transactionFolder->save();
        }
        $transaction->setParent($transactionFolder);
        $transaction->setEmployee($user);
        $transaction->setDate(Carbon::now("CEST"));
        $objectArray = [];
        foreach($products as $product=>$number){
            $product= DataObject\Product::getByPath("/Products/$product");
            if($product->getPrice()<0||$number<0){
                return $this->json([
                    'success'=>false,
                    'message'=>'swiper no swiping'
                ]);
            }
            $objectMetadata  = new DataObject\Data\ObjectMetadata('purchasedItems', ['number','sum'], $product);
            $objectMetadata ->setNumber($number);
            $objectMetadata ->setSum($number*$product->getPrice()."€");
            $objectArray[] = $objectMetadata;
            $total+=$product->getPrice()*$number;
            $numberOfItems+=$number;
        }
        $transaction->setPurchasedItems($objectArray);
        $transaction->setPrice($total);
        $transaction->setTransactionType('Purchase');
        $transaction->setKey(Carbon::now('CEST')->format("d.m.Y-h:i:s"));

        $newBalance=$user->getMoney()-$total;
        $user->setMoney($newBalance);
        $transaction->setCurrentBalance($newBalance);
        try {
            $transaction->setPublished(true);
            $transaction->save();
            $userTransactions=$user->getTransactions();
            array_unshift($userTransactions,$transaction);
            $user->setTransactions($userTransactions);
            $user->save();

        }catch (Exception $e){
            return $this->json(['success'=>false, 'msg'=>$e]);
        }

        return $this->json([
            'success'=>true,
            'transactionId'=>$transaction->getId(),
            ]);
    }

    /**
     * @Route("/checkout/{station}/{transactionId}", methods="GET")
     * @throws Exception
     */
    public function purchaseSuccessful($station,$transactionId){
        return $this->render('default/purchase.html.twig',[
            'success' => true,
            'transaction' => DataObject\Transaction::getById($transactionId),
            'station' => $station,

            ]);
    }

    /**
     * @Route("/transaction/{station}/{transactionId}", methods="GET")
     * @throws Exception
     */
    public function transactionInfo($station,$transactionId){
        return $this->render('default/transactionInfo.html.twig',[
            'success' => true,
            'transaction' => DataObject\Transaction::getById($transactionId),
            'station' => $station,

        ]);
    }

    /**
     * @Route("/shame", methods="GET")
     * @throws Exception
     */
    public function shame(){

        $apiKey = 'AIzaSyAUii3_-jw5mx5CEWGwkorGbpqeq3WjRTw';
        $keywords = ['shame', 'disappointment', 'bad person', 'you need jesus']; // Replace with the desired keyword

        $randomKey = array_rand($keywords);

// Get the random entry from the array using the random key/index
        $keyword = $keywords[$randomKey];

// Set up the API URL
        $apiUrl = "https://api.tenor.com/v2/search?key={$apiKey}&q=" . urlencode($keyword) . "&limit=10";

// Initialize cURL session
        $ch = curl_init();

// Set cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

// Execute the cURL session and get the response
        $response = curl_exec($ch);

// Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            exit;
        }

// Close cURL session
        curl_close($ch);

// Decode the JSON response
        $data = json_decode($response, true);

// Check if there are results
        if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
            // Get a random index from the results array
            $randomIndex = array_rand($data['results']);

            // Get the random GIF URL
            $randomGifUrl = $data['results'][$randomIndex]['media_formats']['gif']['url'];

            return $this->render('default/shame.html.twig', [
                'gifUrl' => $randomGifUrl
            ]);
        }
        return $this->json(["success"=>false, "msg"=>curl_error($ch)]);
    }

}
