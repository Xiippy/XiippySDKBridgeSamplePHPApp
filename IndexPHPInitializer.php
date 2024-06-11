<?php
// *******************************************************************************************
// Copyright © 2019 Xiippy.ai. All rights reserved. Australian patents awarded. PCT patent pending.
//
// NOTES:
//
// - No payment gateway SDK function is consumed directly. Interfaces are defined out of such interactions and then the interface is implemented for payment gateways. Design the interface with the most common members and data structures between different gateways.
// - A proper factory or provider must instantiate an instance of the interface that is interacted with.
// - Any major change made to SDKs should begin with the c sharp SDK with the mindset to keep the high-level syntax, structures and class names the same to minimise porting efforts to other languages. Do not use language specific features that do not exist in other languages. We are not in the business of doing the same thing from scratch multiple times in different forms.
// - Pascal Case for naming conventions should be used for all languages
// - No secret or passwords or returnedObj must exist in the code when checked in
//
// *******************************************************************************************

namespace Xiippy
{
    require_once __DIR__ .'/vendor/autoload.php';
    require_once __DIR__ .'/vendor/xiippy/posecomsdk/src/Models/PaymentProcessingRequest.php';

    use \Xiippy\POSeComSDK\Light\XiippySDKBridgeApiClient\Constants;
    use \Xiippy\POSeComSDK\Light\XiippySDKBridgeApiClient;
    use \Xiippy\POSeComSDK\Light\Models\PaymentProcessingRequest;
    use \Xiippy\POSeComSDK\Light\Models\PaymentRecordCustomer;
    use \Xiippy\POSeComSDK\Light\Models\IssuerStatementRecord;
    use \Xiippy\POSeComSDK\Light\Models\PaymentRecordCustomerAddress;
    use \Xiippy\POSeComSDK\Light\Models\StatementItem;

// you need to generate these values after logging into your locally-deployed instance of the Xiippy SDKBridge, or, alternatively, the cloud-hosted one at https://sdkbridge.xiippy.ai.
// secrets have to be treated like secrets
// how to persist them and load them is all up to the developers

    define("Config_BaseAddress" , "The base URL of the SDK Bridge instance");  // the base address when using the cloud hosted SDK bridge is https://sdkbridge.xiippy.ai
    define("MerchantGroupID" , "Your Merchant Group ID, as reported by the SDK Bridge instance");
    define("Config_ApiKey" , "API Secret Key, as reported by the SDK Bridge instance");
    define("MerchantID" , "Your Merchant ID, as reported by the SDK Bridge instance");


    class IndexPHPInitializer
    {
        public static function GUID()
        {
            if (function_exists('com_create_guid') === true)
            {
                return trim(com_create_guid(), '{}');
            }
            return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        }


        public static function build_http_query( $query ){
            $query_array = array();
            foreach( $query as $key => $key_value ){
                $query_array[] = urlencode( $key ) . '=' . urlencode( $key_value );
            }
            return implode( '&', $query_array );
        }


        public static function InitiatePaymentNGetiFrameUrl()
        {

            $UniqueID = IndexPHPInitializer::GUID();

            $req = new PaymentProcessingRequest();
            $req->MerchantGroupID = MerchantGroupID;
            $req->MerchantID = MerchantID;
            $req->Amount = 2.5;
            $req->Currency = 'aud';
            $req->ExternalUniqueID = $UniqueID;
            $req->IsPreAuth = false;
            $req->IsViaTerminal = false;

            $req->Customer = new PaymentRecordCustomer();
            $req->Customer->CustomerAddress = new PaymentRecordCustomerAddress();
            $req->Customer->CustomerAddress->CityOrSuburb='Brisbane';
            $req->Customer->CustomerAddress->Country='Australia';
            $req->Customer->CustomerAddress->FullName='Full Name';
            $req->Customer->CustomerAddress->Line1='100 Queen St';
            $req->Customer->CustomerAddress->PhoneNumber='+61400000000';
            $req->Customer->CustomerAddress->PostalCode='4000';
            $req->Customer->CustomerAddress->StateOrPrivince='Qld';
            $req->Customer->CustomerEmail = 'dont@contact.me';
            $req->Customer->CustomerName = 'Full Name';
            $req->Customer->CustomerPhone = '+61400000000';

            $req->IssuerStatementRecord = new IssuerStatementRecord();
            $req->IssuerStatementRecord->UniqueStatementID = $UniqueID;
            $req->IssuerStatementRecord->RandomStatementID = POSeComSDK::GUID();
            $req->IssuerStatementRecord->StatementCreationDate = '0';
            $req->IssuerStatementRecord->StatementTimeStamp = (new \DateTime('NOW'))->format('YmdHis'); // important!
            $req->IssuerStatementRecord->ProtocolVersion = 1;
            $req->IssuerStatementRecord->Description = 'Test transaction #1';
            $req->IssuerStatementRecord->DetailsInBodyBeforeItems = 'Description on the receipt before items';
            $req->IssuerStatementRecord->DetailsInBodyAfterItems = 'Description on the receipt after items';
            $req->IssuerStatementRecord->DetailsInHeader = 'Description on the footer';
            $req->IssuerStatementRecord->DetailsInFooter = 'Description on the header';

            $StatementItem  = new StatementItem();
            $StatementItem->Description = 'Description2';
            $StatementItem->UnitPrice = 33;
            $StatementItem->Url = 'Url2';
            $StatementItem->Quantity = 1;
            $StatementItem->Identifier = 'Identifier2';
            $StatementItem->Tax = 3;
            $StatementItem->TotalPrice = 33;
            $req->IssuerStatementRecord->StatementItems[]=$StatementItem;

            $req->IssuerStatementRecord->TotalAmount = 44;
            $req->IssuerStatementRecord->TotalTaxAmount = 4;


            $XiippySDKBridgeApiClient= new XiippySDKBridgeApiClient(true, Config_ApiKey, Config_BaseAddress.InitiateXiippyPaymentPath, MerchantID, MerchantGroupID);
            $keys = $XiippySDKBridgeApiClient->InitiateXiippyPayment($req);

            $result = array(Constants::$QueryStringParam_spw=>"true");
            $result += array(Constants::$QueryStringParam_ShowLongXiippyText=>"true");

            $result+= array(Constants::$QueryStringParam_MerchantID=> MerchantID);
            $result+= array(Constants::$QueryStringParam_MerchantGroupID=> MerchantGroupID);

            if(isset($keys->clientAuthenticator)){
                $result+= array(Constants::$QueryStringParam_ca=> $keys->clientAuthenticator);
            }

            if(isset($keys->randomStatementID)){
                $result+= array(Constants::$QueryStringParam_rsid=>$keys->randomStatementID);
            }

            if(isset($keys->statementTimeStamp)){
                $result+= array(Constants::$QueryStringParam_sts=> $keys->statementTimeStamp);
            }

            if(isset($keys->clientSecret)){
                $result+= array(Constants::$QueryStringParam_cs=> $keys->clientSecret);
            }
           

            $retval= Config_BaseAddress."/Payments/Process?".POSeComSDK::build_http_query($result);
            return $retval;
        }
    }
}