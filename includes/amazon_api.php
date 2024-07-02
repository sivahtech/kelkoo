<?php
function getAmazonProductsbybrand($search,$page)
{
  
   $pagno=(int)$page;
  //  die();
    $accessKey=get_option('amazon_access_key');
    if($accessKey==''){
        
    }
    $secretKey=get_option('amazon_secret_key');
    if($secretKey==''){
       
    }
    $regionName=get_option('amazon_region_name');
    if($regionName==''){
        $regionName = 'eu-west-1';
    }
    
    $serviceName = 'ProductAdvertisingAPI';
    $path = '/paapi5/searchitems';
    $httpMethodName = 'POST';
    $host = "webservices.amazon.it";
    $xAmzDate = gmdate("Ymd\THis\Z");
    $currentDate = gmdate("Ymd");
    $reserachdata=[
        
        "Resources" => [
            "BrowseNodeInfo.BrowseNodes",
         //   "BrowseNodeInfo.BrowseNodes.Ancestor",
         //   "BrowseNodeInfo.BrowseNodes.SalesRank",
         //   "BrowseNodeInfo.WebsiteSalesRank",
            "CustomerReviews.Count",
            "CustomerReviews.StarRating",
            "Images.Primary.Small",
            "Images.Primary.Medium",
            "Images.Primary.Large",
            "Images.Primary.HighRes",
            "Images.Variants.Small",
            "Images.Variants.Medium",
            "Images.Variants.Large",
            "Images.Variants.HighRes",
            "ItemInfo.ByLineInfo",
            "ItemInfo.ContentInfo",
            "ItemInfo.ContentRating",
            "ItemInfo.Classifications",
            "ItemInfo.ExternalIds",
            "ItemInfo.Features",
            "ItemInfo.ManufactureInfo",
            "ItemInfo.ProductInfo",
            "ItemInfo.TechnicalInfo",
            "ItemInfo.Title",
            "ItemInfo.TradeInInfo",
            "Offers.Listings.Availability.MaxOrderQuantity",
            "Offers.Listings.Availability.Message",
            "Offers.Listings.Availability.MinOrderQuantity",
            "Offers.Listings.Availability.Type",
            "Offers.Listings.Condition",
            "Offers.Listings.Condition.ConditionNote",
            "Offers.Listings.Condition.SubCondition",
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "Offers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "Offers.Listings.DeliveryInfo.IsPrimeEligible",
            "Offers.Listings.DeliveryInfo.ShippingCharges",
            "Offers.Listings.IsBuyBoxWinner",
            "Offers.Listings.LoyaltyPoints.Points",
            "Offers.Listings.MerchantInfo",
            "Offers.Listings.Price",
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive",
            "Offers.Listings.ProgramEligibility.IsPrimePantry",
            "Offers.Listings.Promotions",
            "Offers.Listings.SavingBasis",
            "Offers.Summaries.HighestPrice",
            "Offers.Summaries.LowestPrice",
            "Offers.Summaries.OfferCount",
            "ParentASIN",
            "RentalOffers.Listings.Availability.MaxOrderQuantity",
            "RentalOffers.Listings.Availability.Message",
            "RentalOffers.Listings.Availability.MinOrderQuantity",
            "RentalOffers.Listings.Availability.Type",
            "RentalOffers.Listings.BasePrice",
            "RentalOffers.Listings.Condition",
            "RentalOffers.Listings.Condition.ConditionNote",
            "RentalOffers.Listings.Condition.SubCondition",
            "RentalOffers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "RentalOffers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "RentalOffers.Listings.DeliveryInfo.IsPrimeEligible",
            "RentalOffers.Listings.DeliveryInfo.ShippingCharges",
            "RentalOffers.Listings.MerchantInfo",
            "SearchRefinements"
        ],
        "Brand" => $search,
        "PartnerTag" => "webnegozishop-21",
        "PartnerType" => "Associates",
        "Marketplace" => "www.amazon.it",
        "ItemCount" => 10,
        "ItemPage" => $pagno
    ];
			
     $payload = json_encode($reserachdata);

    $headers = [
        'content-encoding' => 'amz-1.0',
        'content-type'     => 'application/json; charset=utf-8',
        'host'             => $host,
        'x-amz-target'     => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
        'x-amz-date'       => $xAmzDate
    ];

    ksort($headers);

    $canonicalURL = prepareCanonicalRequest($httpMethodName, $path, $headers, $payload);
    $stringToSign = prepareStringToSign($canonicalURL, $xAmzDate, $currentDate, $regionName, $serviceName);
    $signature = calculateSignature($stringToSign, $secretKey, $currentDate, $regionName, $serviceName);

    if ($signature) {
        $headers['Authorization'] = buildAuthorizationString($accessKey, $currentDate, $regionName, $serviceName, $headers, $signature);
    }

    $headerString = "";
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }

    $params = array(
        'http' => array(
            'header' => $headerString,
            'method' => 'POST',
            'content' => $payload
        )
    );
    
    $stream = stream_context_create($params);

    $fp = @fopen('https://' . $host . $path, 'rb', false, $stream);

    if (!$fp) {
        $error = error_get_last();
        echo $error['message'];
        die();
        throw new Exception("Failed to open stream: " . $error['message']);
    }

    $response = @stream_get_contents($fp);
    if ($response === false) {
          $error = error_get_last();
        throw new Exception("Failed to get response: " . $error['message']);
    }

    return $response;
}

function getAmazonProductsbycategory($search,$page,$brand)
{
  
    $pagno=(int)$page;

    $accessKey=get_option('amazon_access_key');
    if($accessKey==''){
        
    }
    $secretKey=get_option('amazon_secret_key');
    if($secretKey==''){
       
    }
    $regionName=get_option('amazon_region_name');
    if($regionName==''){
        $regionName = 'eu-west-1';
    }
    $serviceName = 'ProductAdvertisingAPI';
    $path = '/paapi5/searchitems';
    $httpMethodName = 'POST';
    $host = "webservices.amazon.it";
    $xAmzDate = gmdate("Ymd\THis\Z");
    $currentDate = gmdate("Ymd");
    $reserachdata=[
        
        "Resources" => [
            "BrowseNodeInfo.BrowseNodes",
         //   "BrowseNodeInfo.BrowseNodes.Ancestor",
         //   "BrowseNodeInfo.BrowseNodes.SalesRank",
         //   "BrowseNodeInfo.WebsiteSalesRank",
            "CustomerReviews.Count",
            "CustomerReviews.StarRating",
            "Images.Primary.Small", 
            "Images.Primary.Medium",
            "Images.Primary.Large",
            "Images.Primary.HighRes",
            "Images.Variants.Small",
            "Images.Variants.Medium",
            "Images.Variants.Large",
            "Images.Variants.HighRes",
            "ItemInfo.ByLineInfo",
            "ItemInfo.ContentInfo",
            "ItemInfo.ContentRating",
            "ItemInfo.Classifications",
            "ItemInfo.ExternalIds",
            "ItemInfo.Features",
            "ItemInfo.ManufactureInfo",
            "ItemInfo.ProductInfo",
            "ItemInfo.TechnicalInfo",
            "ItemInfo.Title",
            "ItemInfo.TradeInInfo",
            "Offers.Listings.Availability.MaxOrderQuantity",
            "Offers.Listings.Availability.Message",
            "Offers.Listings.Availability.MinOrderQuantity",
            "Offers.Listings.Availability.Type",
            "Offers.Listings.Condition",
            "Offers.Listings.Condition.ConditionNote",
            "Offers.Listings.Condition.SubCondition",
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "Offers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "Offers.Listings.DeliveryInfo.IsPrimeEligible",
            "Offers.Listings.DeliveryInfo.ShippingCharges",
            "Offers.Listings.IsBuyBoxWinner",
            "Offers.Listings.LoyaltyPoints.Points",
            "Offers.Listings.MerchantInfo",
            "Offers.Listings.Price",
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive",
            "Offers.Listings.ProgramEligibility.IsPrimePantry",
            "Offers.Listings.Promotions",
            "Offers.Listings.SavingBasis",
            "Offers.Summaries.HighestPrice",
            "Offers.Summaries.LowestPrice",
            "Offers.Summaries.OfferCount",
            "ParentASIN",
            "RentalOffers.Listings.Availability.MaxOrderQuantity",
            "RentalOffers.Listings.Availability.Message",
            "RentalOffers.Listings.Availability.MinOrderQuantity",
            "RentalOffers.Listings.Availability.Type",
            "RentalOffers.Listings.BasePrice",
            "RentalOffers.Listings.Condition",
            "RentalOffers.Listings.Condition.ConditionNote",
            "RentalOffers.Listings.Condition.SubCondition",
            "RentalOffers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "RentalOffers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "RentalOffers.Listings.DeliveryInfo.IsPrimeEligible",
            "RentalOffers.Listings.DeliveryInfo.ShippingCharges",
            "RentalOffers.Listings.MerchantInfo",
            "SearchRefinements"
        ],
        "Keywords" => $search,
        "PartnerTag" => "webnegozishop-21",
        "PartnerType" => "Associates",
        "Marketplace" => "www.amazon.it",
        "ItemCount" => 10,
        "ItemPage" => $pagno,
        "Operation"=> "SearchItems"
    ];
    
    if($brand){
        $reserachdata['Brand'] = $brand;
    }

     $payload = json_encode($reserachdata);

    $headers = [
        'content-encoding' => 'amz-1.0',
        'content-type'     => 'application/json; charset=utf-8',
        'host'             => $host,
        'x-amz-target'     => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
        'x-amz-date'       => $xAmzDate
    ];

    ksort($headers);

    $canonicalURL = prepareCanonicalRequest($httpMethodName, $path, $headers, $payload);
    $stringToSign = prepareStringToSign($canonicalURL, $xAmzDate, $currentDate, $regionName, $serviceName);
    $signature = calculateSignature($stringToSign, $secretKey, $currentDate, $regionName, $serviceName);

    if ($signature) {
        $headers['Authorization'] = buildAuthorizationString($accessKey, $currentDate, $regionName, $serviceName, $headers, $signature);
    }

    $headerString = "";
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }

    $params = array(
        'http' => array(
            'header' => $headerString,
            'method' => 'POST',
            'content' => $payload
        )
    );
    
    $stream = stream_context_create($params);

    $fp = @fopen('https://' . $host . $path, 'rb', false, $stream);

    if (!$fp) {
        $error = error_get_last();
        echo $error['message'];
       
        throw new Exception("Failed to open stream: " . $error['message']);
    }

    $response = @stream_get_contents($fp);
    if ($response === false) {
          $error = error_get_last();
        throw new Exception("Failed to get response: " . $error['message']);
    }

    return $response;
}


function getAmazonProducts($search,$page)
{
   $pagno=(int)$page;
  //  die();
    $accessKey=get_option('amazon_access_key');
    if($accessKey==''){
        
    }
    $secretKey=get_option('amazon_secret_key');
    if($secretKey==''){
       
    }
    $regionName=get_option('amazon_region_name');
    if($regionName==''){
        $regionName = 'eu-west-1';
    }
    $serviceName = 'ProductAdvertisingAPI';
    $path = '/paapi5/searchitems';
    $httpMethodName = 'POST';
    $host = "webservices.amazon.it";
    $xAmzDate = gmdate("Ymd\THis\Z");
    $currentDate = gmdate("Ymd");
    $reserachdata=[
        "Keywords" => $search,
        "Resources" => [
            "BrowseNodeInfo.BrowseNodes",
            // "BrowseNodeInfo.BrowseNodes.Ancestor",
         //   "BrowseNodeInfo.BrowseNodes.SalesRank",
         //   "BrowseNodeInfo.WebsiteSalesRank",
            "CustomerReviews.Count",
            "CustomerReviews.StarRating",
            "Images.Primary.Small",
            "Images.Primary.Medium",
            "Images.Primary.Large",
            "Images.Primary.HighRes",
            "Images.Variants.Small",
            "Images.Variants.Medium",
            "Images.Variants.Large",
            "Images.Variants.HighRes",
            "ItemInfo.ByLineInfo",
            "ItemInfo.ContentInfo",
            "ItemInfo.ContentRating",
            "ItemInfo.Classifications",
            "ItemInfo.ExternalIds",
            "ItemInfo.Features",
            "ItemInfo.ManufactureInfo",
            "ItemInfo.ProductInfo",
            "ItemInfo.TechnicalInfo",
            "ItemInfo.Title",
            "ItemInfo.TradeInInfo",
            "Offers.Listings.Availability.MaxOrderQuantity",
            "Offers.Listings.Availability.Message",
            "Offers.Listings.Availability.MinOrderQuantity",
            "Offers.Listings.Availability.Type",
            "Offers.Listings.Condition",
            "Offers.Listings.Condition.ConditionNote",
            "Offers.Listings.Condition.SubCondition",
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "Offers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "Offers.Listings.DeliveryInfo.IsPrimeEligible",
            "Offers.Listings.DeliveryInfo.ShippingCharges",
            "Offers.Listings.IsBuyBoxWinner",
            "Offers.Listings.LoyaltyPoints.Points",
            "Offers.Listings.MerchantInfo",
            "Offers.Listings.Price",
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive",
            "Offers.Listings.ProgramEligibility.IsPrimePantry",
            "Offers.Listings.Promotions",
            "Offers.Listings.SavingBasis",
            "Offers.Summaries.HighestPrice",
            "Offers.Summaries.LowestPrice",
            "Offers.Summaries.OfferCount",
            "ParentASIN",
            "RentalOffers.Listings.Availability.MaxOrderQuantity",
            "RentalOffers.Listings.Availability.Message",
            "RentalOffers.Listings.Availability.MinOrderQuantity",
            "RentalOffers.Listings.Availability.Type",
            "RentalOffers.Listings.BasePrice",
            "RentalOffers.Listings.Condition",
            "RentalOffers.Listings.Condition.ConditionNote",
            "RentalOffers.Listings.Condition.SubCondition",
            "RentalOffers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "RentalOffers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "RentalOffers.Listings.DeliveryInfo.IsPrimeEligible",
            "RentalOffers.Listings.DeliveryInfo.ShippingCharges",
            "RentalOffers.Listings.MerchantInfo",
            "SearchRefinements"
        ],
        "PartnerTag" => "webnegozishop-21",
        "PartnerType" => "Associates",
        "Marketplace" => "www.amazon.it",
        "ItemCount" => 10,
        "ItemPage" => $pagno
    ];
			
    $payload = json_encode($reserachdata);

    $headers = [
        'content-encoding' => 'amz-1.0',
        'content-type'     => 'application/json; charset=utf-8',
        'host'             => $host,
        'x-amz-target'     => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
        'x-amz-date'       => $xAmzDate
    ];

    ksort($headers);

    $canonicalURL = prepareCanonicalRequest($httpMethodName, $path, $headers, $payload);
    $stringToSign = prepareStringToSign($canonicalURL, $xAmzDate, $currentDate, $regionName, $serviceName);
    $signature = calculateSignature($stringToSign, $secretKey, $currentDate, $regionName, $serviceName);

    if ($signature) {
        $headers['Authorization'] = buildAuthorizationString($accessKey, $currentDate, $regionName, $serviceName, $headers, $signature);
    }

    $headerString = "";
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }

    $params = array(
        'http' => array(
            'header' => $headerString,
            'method' => 'POST',
            'content' => $payload
        )
    );
    
    $stream = stream_context_create($params);

    $fp = @fopen('https://' . $host . $path, 'rb', false, $stream);

    if (!$fp) {
        $error = error_get_last();
        throw new Exception("Failed to open stream: " . $error['message']);
    }

    $response = @stream_get_contents($fp);
    if ($response === false) {
          $error = error_get_last();
        throw new Exception("Failed to get response: " . $error['message']);
    }

    return $response;
}

function getAmazonProduct($ASIN)
{
    $accessKey=get_option('amazon_access_key');
    if($accessKey==''){
        
    }
    $secretKey=get_option('amazon_secret_key');
    if($secretKey==''){
       
    }
    $regionName=get_option('amazon_region_name');
    if($regionName==''){
        $regionName = 'eu-west-1';
    }
    $serviceName = 'ProductAdvertisingAPI';
    $path = '/paapi5/getitems';
    $httpMethodName = 'POST';
    $host = "webservices.amazon.it";
    $xAmzDate = gmdate("Ymd\THis\Z");
    $currentDate = gmdate("Ymd");
    $reserachdata=[
        "ItemIds"=>[$ASIN],
        "Resources" => [
            "BrowseNodeInfo.BrowseNodes",
            "Images.Primary.Small",
            "Images.Primary.Medium",
            "Images.Primary.Large",
            "Images.Primary.HighRes",
            "ItemInfo.ByLineInfo",
            "ItemInfo.ContentInfo",
            "ItemInfo.ContentRating",
            "ItemInfo.Classifications",
            "ItemInfo.ExternalIds",
            "ItemInfo.Features",
            "ItemInfo.ManufactureInfo",
            "ItemInfo.ProductInfo",
            "ItemInfo.TechnicalInfo",
            "ItemInfo.Title",
            "ItemInfo.TradeInInfo",
            "Offers.Listings.Availability.MaxOrderQuantity",
            "Offers.Listings.Availability.Message",
            "Offers.Listings.Availability.MinOrderQuantity",
            "Offers.Listings.Availability.Type",
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "Offers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "Offers.Listings.DeliveryInfo.IsPrimeEligible",
            "Offers.Listings.DeliveryInfo.ShippingCharges",
            "Offers.Listings.IsBuyBoxWinner",
            "Offers.Listings.LoyaltyPoints.Points",
            "Offers.Listings.MerchantInfo",
            "Offers.Listings.Price",
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive",
            "Offers.Listings.ProgramEligibility.IsPrimePantry",
            "Offers.Listings.Promotions",
            "Offers.Listings.SavingBasis",
            "Offers.Summaries.HighestPrice",
            "Offers.Summaries.LowestPrice",
            "Offers.Summaries.OfferCount",
            "ParentASIN",
            "RentalOffers.Listings.Availability.MaxOrderQuantity",
            "RentalOffers.Listings.Availability.Message",
            "RentalOffers.Listings.Availability.MinOrderQuantity",
            "RentalOffers.Listings.Availability.Type",
            "RentalOffers.Listings.BasePrice",
            ],
        "PartnerTag" => "webnegozishop-21",
        "PartnerType" => "Associates",
        "Marketplace" => "www.amazon.it",
        "Operation"=> "GetItems",
        ];
			
    $payload = json_encode($reserachdata);

    $headers = [
        'content-encoding' => 'amz-1.0',
        'content-type'     => 'application/json; charset=utf-8',
        'host'             => $host,
        'x-amz-target'     => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
        'x-amz-date'       => $xAmzDate
    ];

    ksort($headers);

    $canonicalURL = prepareCanonicalRequest($httpMethodName, $path, $headers, $payload);
    $stringToSign = prepareStringToSign($canonicalURL, $xAmzDate, $currentDate, $regionName, $serviceName);
    $signature = calculateSignature($stringToSign, $secretKey, $currentDate, $regionName, $serviceName);

    if ($signature) {
        $headers['Authorization'] = buildAuthorizationString($accessKey, $currentDate, $regionName, $serviceName, $headers, $signature);
    }

    $headerString = "";
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
    
    $params = array(
        'http' => array(
            'header' => $headerString,
            'method' => 'POST',
            'content' => $payload
        )
    );
    
    $stream = stream_context_create($params);

    $fp = @fopen('https://' . $host . $path, 'rb', false, $stream);

    if (!$fp) {
        $error = error_get_last();
        throw new Exception("Failed to open stream: " . $error['message']);
    }

    $response = @stream_get_contents($fp);
    if ($response === false) {
          $error = error_get_last();
        throw new Exception("Failed to get response: " . $error['message']);
    }

    return $response;

}





function prepareCanonicalRequest($httpMethodName, $path, $headers, $payload)
{
    $canonicalURL = $httpMethodName . "\n";
    $canonicalURL .= $path . "\n" . "\n";
    $signedHeaders = '';
    foreach ($headers as $key => $value) {
        $signedHeaders .= $key . ";";
        $canonicalURL .= $key . ":" . $value . "\n";
    }
    $canonicalURL .= "\n";
    $signedHeaders = substr($signedHeaders, 0, -1);
    $canonicalURL .= $signedHeaders . "\n";
    $canonicalURL .= generateHex($payload);
    return $canonicalURL;
}

function prepareStringToSign($canonicalURL, $xAmzDate, $currentDate, $regionName, $serviceName)
{
    $stringToSign = '';
    $stringToSign .= "AWS4-HMAC-SHA256\n";
    $stringToSign .= $xAmzDate . "\n";
    $stringToSign .= $currentDate . "/" . $regionName . "/" . $serviceName . "/aws4_request\n";
    $stringToSign .= generateHex($canonicalURL);
    return $stringToSign;
}

function calculateSignature($stringToSign, $secretKey, $currentDate, $regionName, $serviceName)
{
    $signatureKey = getSignatureKey($secretKey, $currentDate, $regionName, $serviceName);
    $signature = hash_hmac("sha256", $stringToSign, $signatureKey, true);
    return strtolower(bin2hex($signature));
}

function buildAuthorizationString($accessKey, $currentDate, $regionName, $serviceName, $headers, $signature)
{
    $signedHeaders = implode(';', array_keys($headers));
    return "AWS4-HMAC-SHA256 Credential=$accessKey/$currentDate/$regionName/$serviceName/aws4_request,SignedHeaders=$signedHeaders,Signature=$signature";
}

function generateHex($data)
{
    return strtolower(bin2hex(hash("sha256", $data, true)));
}

function getSignatureKey($key, $date, $regionName, $serviceName)
{
    $kSecret = "AWS4" . $key;
    $kDate = hash_hmac("sha256", $date, $kSecret, true);
    $kRegion = hash_hmac("sha256", $regionName, $kDate, true);
    $kService = hash_hmac("sha256", $serviceName, $kRegion, true);
    $kSigning = hash_hmac("sha256", "aws4_request", $kService, true);
    return $kSigning;
}
