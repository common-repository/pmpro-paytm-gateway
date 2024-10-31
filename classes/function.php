<?php
function pmproPaytm_encrypt($input, $ky)
{
    $key   = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    $data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
    return $data;
}

function pmproPaytm_decrypt($crypt, $ky)
{
    $key   = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    $data = openssl_decrypt ( $crypt , "AES-128-CBC" , $key, 0, $iv );
    return $data;
}

function pmproPaytm_pkcs5_pad($text, $blocksize)
{
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

function pmproPaytm_pkcs5_unpad($text)
{
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text))
        return false;
    return substr($text, 0, -1 * $pad);
}

function pmproPaytm_generateRandString($length)
{
    $random = "";
    srand((double) microtime() * 1000000);
    
    $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
    $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
    $data .= "0FGH45OP89";
    
    for ($i = 0; $i < $length; $i++) {
        $random .= substr($data, (rand() % (strlen($data))), 1);
    }
    
    return $random;
}

function pmproPaytm_checkString($value)
{
    $myvalue = ltrim($value);
    $myvalue = rtrim($myvalue);
    if ($myvalue == 'null')
        $myvalue = '';
    return $myvalue;
}

function pmproPaytm_getChecksumFromArray($arrayList, $key, $sort = 1)
{
    if ($sort != 0) {
        ksort($arrayList);
    }
    $str         = pmproPaytm_getArraytoStr($arrayList);
    $rand        = pmproPaytm_generateRandString(4);
    $finalString = $str . "|" . $rand;
    $hash        = hash("sha256", $finalString);
    $hashString  = $hash . $rand;
    $checksum    = pmproPaytm_encrypt($hashString, $key);
    return $checksum;
}

function pmproPaytm_verifychecksum($arrayList, $key, $checksumvalue)
{
    $arrayList = pmproPaytm_removeCheckSumParam($arrayList);
    ksort($arrayList);
    $str        = pmproPaytm_getArraytoStrForVerify($arrayList);
    $paytm_hash = pmproPaytm_decrypt($checksumvalue, $key);
    $salt       = substr($paytm_hash, -4);
    
    $finalString = $str . "|" . $salt;
    
    $website_hash = hash("sha256", $finalString);
    $website_hash .= $salt;
    
    $validFlag = "FALSE";
    if ($website_hash == $paytm_hash) {
        $validFlag = "TRUE";
    } else {
        $validFlag = "FALSE";
    }
    return $validFlag;
}

function pmproPaytm_getArraytoStr($arrayList) {
	$findme   = 'REFUND';
	$findmepipe = '|';
	$paramStr = "";
	$flag = 1;	
	foreach ($arrayList as $key => $value) {
		$pos = strpos($value, $findme);
		$pospipe = strpos($value, $findmepipe);
		if ($pos !== false || $pospipe !== false) 
		{
			continue;
		}
		
		if ($flag) {
			$paramStr .= pmproPaytm_checkString($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . pmproPaytm_checkString($value);
		}
	}
	return $paramStr;
}

function pmproPaytm_getArraytoStrForVerify($arrayList) {
	$paramStr = "";
	$flag = 1;
	foreach ($arrayList as $key => $value) {
		if ($flag) {
			$paramStr .= pmproPaytm_checkString($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . pmproPaytm_checkString($value);
		}
	}
	return $paramStr;
}

function pmproPaytm_redirect2PG($paramList, $key)
{
    $hashString = pmproPaytm_getChecksumFromArray($paramList);
    $checksum   = pmproPaytm_encrypt($hashString, $key);
}

function pmproPaytm_removeCheckSumParam($arrayList)
{
    if (isset($arrayList["CHECKSUMHASH"])) {
        unset($arrayList["CHECKSUMHASH"]);
    }
    return $arrayList;
}

function pmproPaytm_getTxnStatus($requestParamList)
{
    return pmproPaytm_get_paytm_response_url(PAYTM_STATUS_QUERY_URL, $requestParamList);
}

function pmproPaytm_initiateTxnRefund($requestParamList)
{
    $CHECKSUM                     = pmproPaytm_getChecksumFromArray($requestParamList, PAYTM_MERCHANT_KEY, 0);
    $requestParamList["CHECKSUM"] = $CHECKSUM;
    return pmproPaytm_get_paytm_response_url(PAYTM_REFUND_URL, $requestParamList);
}

function pmproPaytm_get_paytm_response_url($apiURL, $requestParamList)
{
    $jsonResponse      = "";
    $responseParamList = array();
    $JsonData          = json_encode($requestParamList);
    $apiURL = $apiURL.'?JsonData='.urlencode($JsonData);
    $response = wp_remote_get( $apiURL);
    $responseParamList = json_decode($response['body'], true);
    return $responseParamList;
}

function pmproPaytm_ChecksumParamPattern($param)
{
    $pattern[0]     = "%,%";
    $pattern[1]     = "%#%";
    $pattern[2]     = "%\(%";
    $pattern[3]     = "%\)%";
    $pattern[4]     = "%\{%";
    $pattern[5]     = "%\}%";
    $pattern[6]     = "%<%";
    $pattern[7]     = "%>%";
    $pattern[8]     = "%`%";
    $pattern[9]     = "%!%";
    $pattern[10]    = "%\\$%";
    $pattern[11]    = "%\%%";
    $pattern[12]    = "%\^%";
    $pattern[13]    = "%=%";
    $pattern[14]    = "%\+%";
    $pattern[15]    = "%\|%";
    $pattern[16]    = "%\\\%";
    $pattern[17]    = "%:%";
    $pattern[18]    = "%'%";
    $pattern[19]    = "%\"%";
    $pattern[20]    = "%;%";
    $pattern[21]    = "%~%";
    $pattern[22]    = "%\[%";
    $pattern[23]    = "%\]%";
    $pattern[24]    = "%\*%";
    $pattern[25]    = "%&%";
    $ChecksumParamPattern = preg_replace($pattern, "", $param);
    return $ChecksumParamPattern;
}