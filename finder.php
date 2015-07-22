<?php

////                ////////               formats search query keywords
if (strpos($search_phrase,'Knives') !== false) {

	//str_replace("Knife", "Knives", $search_phrase);
}
if (strpos($search_phrase,'Knives') == false) {

	if (strpos($search_phrase,'Knife') == false) {
	$search_phrase=$search_phrase." Knives";

}
	if (strpos($search_phrase,'Knife') !== false) {
	//$search_phrase = rtrim($search_phrase);

$search_phrase = str_replace("Knife","Knives", $search_phrase);

}
	
}
//////////                           /////////////determine brand
$brand_search = $_POST['search'];
///////////////
$search_q =$search_phrase;
//////////////                                        ////////prosperent api
$api_key = 'prosperent_key';
//
//$prosper_query  =str_replace(" ", "*", $search_phrase);
$prosper_query =urlencode($search_phrase);
$url = 'http://api.prosperent.com/api/search?api_key=".$api_key."&query='.$prosper_query.'&limit=20&sortBy=price&relevancyThreshold=1&filterMerchant=!Smoky*|!KitchenSource*|!JPTheMint*&limit=20&minPrice='.$minprice.'&filterKeyword='.$word.'*knives';

$curl = curl_init();

// Set options
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $url,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 30
));

// Send the request
$response = curl_exec($curl);

// Close request
curl_close($curl);

// Convert the json response to an array
$response = json_decode($response, true);
$prosper_total = $response['totalRecordsFound'];
// Check for errors

if (count($response['errors']))
{
    //throw new Exception(implode('; ', $response['errors']));
	$err_msg = "";
}

// Set specified data from response
$prosperent_data = $response['data'];
$prosperent_total = $response['totalRecordsFound'];
//print_r($prosperent_data);

/////////////////////////////////////////////
//////////////////////////           /////////                     ebay api
$ebay_keyword = $search_phrase;
$ebay_keyword = $ebay_keyword." -sheath";
$safequery = urlencode($ebay_keyword);  // Make the query URL-friendly

$url = "http://svcs.ebay.com/services/search/FindingService/v1?" . 
        "OPERATION-NAME=findItemsByKeywords&" . 
        "SERVICE-VERSION=1.0.0&" .
        "SECURITY-APPNAME=ebay_appname&" .
		 "RESPONSE-DATA-FORMAT=JSON&" . 
        "REST-PAYLOAD&" . 
		"affiliate.networkId=9&" .
		"affiliate.trackingId=5337705511&" .	
		"affiliate.customId=folding-knivess&" .
		"HideDuplicateItems=True&" .
        "itemFilter(0).name=Condition&" . 
		"itemFilter(0).value(0)=New&" .
		//"itemFilter(0).value(1)=Used&" .
	   	"itemFilter(1).name=ListingType&".
      "itemFilter(1).value(0)=AuctionWithBIN&".
       "itemFilter(1).value(1)=FixedPrice&" .
	      "itemFilter(2).name=MinPrice&".
      "itemFilter(2).value(0)=".$minprice."&".
	  "paginationInput.entriesPerPage=10&" .
	  "keywords=" .$safequery;
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$output = curl_exec($ch); 
curl_close($ch);     

$ebay_data = json_decode($output, true);
//print_r($ebay_data);
$status = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][1]['title'][0];
$ebay_total = $ebay_data['findItemsByKeywordsResponse'][0]['paginationOutput'][0]['totalEntries'][0];
if($ebay_total<1){
	
}
//print_r($ebay_data);
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////                            amazon api 
$azonprice=($minprice*100);
$azonprice=(int)$azonprice;
//echo $azonprice;
//amazon api 
define('AWS_ACCESS_KEY_ID', 'amazon_id'); 
define('AWS_SECRET_ACCESS_KEY', 'amazon_key'); 
define('AMAZON_ASSOC_TAG', 'amazon_tag'); 
define('AWS_MIN_PRICE' , ''.$azonprice.'');
//define('AWS_PAGE_NUM' , ''.$page.'');
function amazon_get_signed_url($searchTerm) { 
$base_url = "http://ecs.amazonaws.com/onca/xml"; 
$params = array( 
'AWSAccessKeyId' => AWS_ACCESS_KEY_ID, 
'AssociateTag' => AMAZON_ASSOC_TAG, 
'Version' => "2013-08-01", 
'Operation' => "ItemSearch", 
'Service' => "AWSECommerceService", 
'ResponseGroup' => "ItemAttributes,Images,OfferFull", 
'Availability' => "Available", 
'Condition' => "New", 
'Operation' => "ItemSearch", 
'SearchIndex' => 'SportingGoods', //Change search index if required, you can also accept it as a parameter for the current method like $searchTerm 
'BrowseNode' => '3400881',
'Sort' => "",
'MinimumPrice' => AWS_MIN_PRICE, 
'Keywords' => $searchTerm);
 

//'ResponseGroup'=>"Images,ItemAttributes,EditorialReview", 
if(empty($params['AssociateTag'])) { 
unset($params['AssociateTag']); 
} 
// Add the Timestamp 
$params['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", 
time()); 

// Sort the URL parameters 
$url_parts = array(); 
foreach(array_keys($params) as $key) 
$url_parts[] = $key . "=" . str_replace('%7E', '~', 
rawurlencode($params[$key])); 
sort($url_parts); 

// Construct the string to sign 
$url_string = implode("&", $url_parts);
$string_to_sign = "GET\necs.amazonaws.com\n/onca/xml\n" . 
$url_string; 

// Sign the request 
$signature = hash_hmac("sha256", $string_to_sign, 
AWS_SECRET_ACCESS_KEY, TRUE); 

// Base64 encode the signature and make it URL safe 
$signature = urlencode(base64_encode($signature)); 
$url = $base_url . '?' . $url_string . "&Signature=" . $signature; return ($url); 
}       ///end amazon function
///////////
$queryString = urlencode($search_phrase);
$getthis = $queryString; 
$show = amazon_get_signed_url($getthis); 
$ch = curl_init($show); 
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
$c = curl_exec($ch); 

$xml = simplexml_load_string($c); 
$json = json_encode($xml); 
$amazon_data = json_decode($json,TRUE); 
//print_r($amazon_data);
/////////               get total search results from amazon
$amazon_total = $amazon_data[Items][TotalResults];
if($amazon_total<1){
	

}
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
$total_products =$prosperent_total+$amazon_total+$ebay_total;
//////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($total_products > 0){
//                 //////////////                prosperent put api data into array
// Loop through the data array
foreach ($prosperent_data as $key => $value)
{
    // Add your own code here to handle the response data
} 

if(count($prosperent_data)){
	foreach($prosperent_data as $product){
		$x = 0;
 		$prosperent_array[$i]['title'] = $product['keyword'];
		$prosperent_array[$i]['image'] = $product['image_url'];
		$prosperent_array[$i]['link'] = $product['affiliate_url'];
		$prosperent_array[$i]['price'] = $product['price'];
		$prosperent_array[$i]['merchant'] = $product['merchant'];
		$prosperent_array[$i]['merchantID'] = $product['merchantId'];
		$i = $i+1;
		
    }
}
//print_r($prosperent_array);
//////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////         ebay load data into array
if($ebay_total<10){
$t=$ebay_total;	
}
else{
$t=10;	
}
///

for($i=0;$i<$t;$i++){ 
//$ebay_array[$i]['brand'] = $query;

$ebay_array[$i]['title'] = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][$i]['title'][0];
$ebay_array[$i]['image'] = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][$i]['galleryURL'][0];
$ebay_array[$i]['link'] = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][$i]['viewItemURL'][0];
$ebay_array[$i]['price'] = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][$i]['sellingStatus'][0]['currentPrice'][0]['__value__'];
$ebay_array[$i]['merchant'] ="ebay";
$ebay_array[$i]['id'] = $ebay_data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'][$i]['itemId'][0];
//$total = $json['findItemsByKeywordsResponse'][0]['paginationOutput'][0]['totalEntries'][0];
}

//print_r($ebay_array);
//////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//print_R($amazon_data);
///////////               amazon put api data into array
if($amazon_total <=10){
$t = $amazon_total;	
}
else{
$t = 10;	
}
for($i=0;$i<$t;$i++){ 
if($amazon_data[Items][Item][$i][Offers][TotalOffers] == 0){
$t = $t-1;	
}

else{
$amazon_array[$i]['title'] = $amazon_data[Items][Item][$i][ItemAttributes][Title];
$amazon_array[$i]['image'] = $amazon_data[Items][Item][$i][MediumImage][URL]; 
$amazon_array[$i]['link'] = $amazon_data[Items][Item][$i][DetailPageURL];//[Offers][MoreOffersUrl];
$amazon_array[$i]['price'] = str_replace('$', '',$amazon_data[Items][Item][$i][ItemAttributes][ListPrice][FormattedPrice]); 
//echo "<br>";
if(empty($amazon_array[$i]['price'])){
$amazon_array[$i]['price'] = str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]);
if(empty($amazon_array[$i]['price'])){
$amazon_array[$i]['price'] = str_replace('$', '',$amazon_data[Items][Item][$i][OfferSummary][LowestNewPrice][FormattedPrice]);
}
}
$r =str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]);
$y = str_replace('$', '',$amazon_data[Items][Item][$i][OfferSummary][LowestNewPrice][FormattedPrice]);

if(str_replace('$', '',$amazon_data[Items][Item][$i][ItemAttributes][ListPrice][FormattedPrice]) >str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]) ){
$amazon_array[$i]['price'] = str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]);
}
if(str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]) > str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]) ){
$amazon_array[$i]['price'] = str_replace('$', '',$amazon_data[Items][Item][$i][Offers][Offer][OfferListing][Price][FormattedPrice]);	
}
$amazon_array[$i]['merchant'] = "amazon"; 
$amazon_array[$i]['discount'] = $amazon_data[Items][Item][$i][Offers][Offer][OfferListing][PercentageSaved];
}
}

//////////////////////////////////////////////////////////////////////////////////////////
for($i=0;$i<count($amazon_array);$i++){
if($amazon_array[$i]['price'] ==""){	
	unset($amazon_array[$i]);
}
}

$amazon_array = array_values($amazon_array);


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////
if(count($prosperent_array)>0 && count($amazon_array) > 0 && count($ebay_array) > 0){
$product_array =array_merge( $ebay_array, $prosperent_array,$amazon_array);
	
}

if(count($prosperent_array)<1 && count($amazon_array) > 0 && count($ebay_array) > 0){
$product_array =array_merge( $ebay_array,$amazon_array);
}
if(count($prosperent_array)<1 && count($amazon_array) < 1 && count($ebay_array) > 0){
$product_array = $ebay_array;	
}
if(count($prosperent_array)>0 && count($amazon_array)< 1 && count($ebay_array) >0){
$product_array = array_merge($prosperent_array, $ebay_array);	

}
if(count($prosperent_array)>0 && count($amazon_array)< 1 && count($ebay_array) < 1){
$product_array =  $prosperent_array;

}
if(count($prosperent_array)>0 && count($amazon_array)> 0 && count($ebay_array) < 1){
$product_array = array_merge($prosperent_array, $amazon_array);	

}
if(count($prosperent_array)<1 && count($amazon_array)> 0 && count($ebay_array) < 1){
$product_array = $amazon_array;	

}
//print_r($product_array);
function order_by_price($a, $b) {
    if ($a["price"] == $b["price"]) {
        return 0;
    }
    else if ($a["price"] < $b["price"]){
        return -1;
    }
    else{
        return 1;
    }
}

usort($product_array, 'order_by_price');

$newArray = array();
$usedFruits = array();
foreach ( $product_array AS $line ) {
if ( !in_array($line['price'], $usedFruits) ) {
        $usedFruits[] = $line['price'];
        $newArray[] = $line;
    }
}
$product_array = $newArray;
unset($newArray,$usedFruits); 
}
$num_entries = count($product_array);
//print_r($amazon_array);

//////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
include('test.php');

?>
<p>Live code example at "<a href="http://www.foldingknifefinder.com">Folding Knife Finder</a></p>
