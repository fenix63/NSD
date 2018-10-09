#!/usr/bin/email.php
<?




// Выведем актуальную корзину для текущего пользователя

$arBasketItems = array();


$DateTo = new DateTime();//Текущая дата 08.10.2018 00:00:00
$DateFrom = new DateTime();
$DateFrom -> add("-30day");//Начальная дата


//Получаем отложенные товары
$dbBasketItems = CSaleBasket::GetList(
        array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
        array(
                "FUSER_ID" => CSaleBasket::GetBasketUserID(),
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL",
                "DELAY" => "Y",
                ">=PROPERTY_DATE" => ConvertDateTime($DateFrom, "YYYY-MM-DD")." 00:00:00",
                "<=PROPERTY_DATE" => ConvertDateTime($DateTo, "YYYY-MM-DD")." 23:59:59",
            ),
        false,
        false,
        array("ID","PRODUCT_ID", "QUANTITY", "DELAY", "PRICE","NAME")
    );
while ($arItems = $dbBasketItems->Fetch())
{

    $delayedItems[] = $arItems;
    $delayedId[] = $arItems['ID'];
}


//Получаем список заказов
$dbBasketItems  =CSaleBasket::GetList(
        array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
        array(
                "FUSER_ID" => CSaleBasket::GetBasketUserID(),
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL",
                ">=PROPERTY_DATE" => ConvertDateTime($DateFrom, "YYYY-MM-DD")." 00:00:00",
                "<=PROPERTY_DATE" => ConvertDateTime($DateTo, "YYYY-MM-DD")." 23:59:59",
            ),
        false,
        false,
        array("ID","PRODUCT_ID", "QUANTITY", "PRICE")
    );

while ($arItems = $dbBasketItems->Fetch())
{

    $orderItems[] = $arItems;
    $orderID[] = $arItems['ID'];
}


$resultArray = array();
//Сравниваем ID списка отложенных товаров, и ID списка заказов.
for($i = 0; $i < count($delayedItems); $i++){
    for($j = 0; $j < count($orderItems); $j++){
        if($delayedItems['ID'] != $orderItems['ID']){
            array_push($resultArray, $delayedItems[$i]);
        }
    }
}




$order = array('sort' => 'asc');
$tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
$rsUsers = CUser::GetList($order, $tmp);//Все пользователи

//Делаем email-рассылку
while ($arUser = $rsUsers->Fetch()) {
    $curEmail = $arUser['EMAIL'];

    $to = $curEmail;
    $subject = 'Вам письмо';
    $msg = 'Добрый день, '.$arUser->GetFullName().'. В вашем вишлисте хранятся товары: <br/>';
    
    foreach ($resultArray as $product) {
        $msg.= $product['NAME'].' Цена' .$product['PRICE'].'<br/>';    
    }

    $headers = array(
        'From' => 'info@nsd.ru',
        'Reply-To' => 'info@nsd.ru',
        'X-Mailer' => 'PHP/' . phpversion()
    );

    mail($to, $subject, $msg, $headers);
}


?>