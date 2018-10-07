<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Шопоголик | Главная");?>

<?
    if(\Bitrix\Main\Loader::includeModule("sale"))
    {
        


       $favFilter = array('ID' => array_flip($_SESSION['USER_FAVORITES']));
       $id_list = array();

       foreach ($favFilter['ID'] as $item) {
           $id_list[] = $item;//тут хранятся id всех отложенных товаров
       }

       
       $arSelect = Array("ID", "IBLOCK_ID","NAME", "DATE_ACTIVE_FROM", "PROPERTY_*");


       $arFilter = Array("IBLOCK_ID"=>8, "ID"=>$id_list[0], "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
       $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
       while($ob = $res->GetNextElement())
       {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

            echo '<pre>';
            var_dump($arFields);
            echo '</pre>';

            echo '-------Свойства-----';

            echo '<pre>';
            var_dump($arProps);
            echo '</pre>';

            //Получаем торговые предложения

            $IBLOCK_ID = 8; //Номер инфоблока с товарами смотрим в админ панели
            $ID = $id_list[0]; 
            $arInfo = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID); 
            if (is_array($arInfo)) 
            {
                //Получаем цены торговых предложений и затем выбираем минимальную цену
                $all_prices = array();
                $rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $ID));
                while ($arOffer = $rsOffers->GetNext())
                {
                    
                    echo "-----------ТП--------<br/>";
                    $rsPrices = CPrice::GetList(array(), array('PRODUCT_ID' => IntVal($arOffer['ID']),'CATALOG_GROUP_ID' => 1) );//номер типа цен смотрим в админ панели
                    if ($arPrice = $rsPrices->Fetch())
                    {
                       var_dump($arPrice["PRICE"]);
                       $all_prices[] = IntVal($arPrice["PRICE"]);
                    }
                    echo "---------/ТП----------<br/>";
                } 
            }



            echo '----------';
        }



    }


?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>