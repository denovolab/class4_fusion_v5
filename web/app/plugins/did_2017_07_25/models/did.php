<?php

class Did extends DidAppModel
{
    var $name = 'Did';
    var $useTable = false;

    public function get_data_count($vendor_id,$client_id,$number,$show_type)
    {
        $encodedWord = base64_encode('DID');
        $where = ["t1.start_date <= now()::date AND (t1.end_date >= now()::date OR t1.end_date IS NULL) AND (client_resource.alias LIKE '%_{$encodedWord}_%' OR client_resource.alias is null)"];
        if ($vendor_id)
            $where[] = "t1.resource_id = $vendor_id";
        if ($client_id)
            $where[] = "t1.client_id = $client_id";
        if ($number)
            $where[] = "t1.code::text like '%{$number}%'";
        if ($show_type == 1)
            $where[] = "t1.resource_id is not null";
        elseif ($show_type == 2)
            $where[] = "t1.resource_id is null";
        $where_sql = '';
        if (!empty($where))
        {
            $where_str = implode(" AND ",$where);
            $where_sql = ' WHERE ' .$where_str;
        }

        $sql = <<<SQL
SELECT count(*) as sum
from
(
SELECT rate.code, did_billing_rel.*, rate.create_time,product_items.update_at, rate.did_type, rate.code_name, rate.country
from did_billing_rel 
INNER JOIN rate ON rate.rate_id = did_billing_rel.rate_id
LEFT JOIN product_items on rate.code = product_items.digits
-- left join product_items_resource ON product_items_resource.item_id = product_items.item_id 
WHERE rate.did_type = 1 order by did_billing_rel.did desc 
) as t1
LEFT JOIN client as vendor_client ON vendor_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.egress_res_id)
LEFT JOIN client as orig_client ON orig_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.ingress_res_id)
LEFT JOIN resource as client_resource ON resource_id = t1.ingress_res_id
$where_sql
SQL;
        //$sql = "select count(*) as sum FROM rate where did_type = 1";
        $data = $this->query($sql);

        return $data[0][0]['sum'];
    }



    public function get_data($vendor_id,$client_id,$number,$show_type,$order_by, $pageSize, $offset)
    {
//        $where = ["t1.end_date IS NULL"];
        $encodedWord = base64_encode('DID');
        $where = ["t1.start_date <= now()::date AND (t1.end_date >= now()::date OR t1.end_date IS NULL) AND (client_resource.alias LIKE '%_{$encodedWord}_%' OR client_resource.alias is null)"];
        if ($vendor_id)
            $where[] = "t1.resource_id = $vendor_id";
        if ($client_id)
            $where[] = "t1.client_id = $client_id";
        if ($number)
            $where[] = "t1.code::text like '%{$number}%'";
        if ($show_type == 1)
            $where[] = "t1.resource_id is not null";
        elseif ($show_type == 2)
            $where[] = "t1.resource_id is null";
        $where_sql = '';
        if (!empty($where))
        {
            $where_str = implode(" AND ",$where);
            $where_sql = ' WHERE ' .$where_str;
        }
        $sql = <<<SQL
SELECT client_resource.alias, t1.id, t1.code, t1.code_name, t1.country, t1.egress_res_id as vendor_id, t1.ingress_res_id as client_id, vendor_client.name as vendor_name,
orig_client.name as client_name, t1.create_time, t1.update_at,
(SELECT ip FROM resource_ip WHERE resource_ip.resource_id = t1.egress_res_id LIMIT 1) as vendor_ip,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as vendor_rule,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.buy_billing_plan_id LIMIT 1) as client_rule,
t1.sell_billing_plan_id as vendor_billing_id,
t1.end_date as end_date,
t1.buy_billing_plan_id as client_billing_id,
t1.start_date as start_date
from
(
SELECT rate.code, did_billing_rel.*, rate.create_time,product_items.update_at, rate.did_type, rate.code_name, rate.country
from did_billing_rel 
INNER JOIN rate ON rate.rate_id = did_billing_rel.rate_id
LEFT JOIN product_items on rate.code = product_items.digits
-- left join product_items_resource ON product_items_resource.item_id = product_items.item_id 
WHERE rate.did_type = 1 order by did_billing_rel.did desc 
) as t1
LEFT JOIN client as vendor_client ON vendor_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.egress_res_id)
LEFT JOIN client as orig_client ON orig_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.ingress_res_id)
LEFT JOIN resource as client_resource ON resource_id = t1.ingress_res_id
$where_sql $order_by 
LIMIT $pageSize OFFSET $offset
SQL;
//        Configure::write('debug', 2);
//        $tmpData = $this->query($sql);
//        echo '<pre>';
//        die(var_dump($tmpData, $sql));

        return $this->query($sql);
    }

    public function get_data_by_client($client_id,$pageSize, $offset){
        $sql = <<<SQL
SELECT rs.alias,rate.code,rate.rate_table_id,rate.code_name,rate.create_time,product_items.update_at,rate.country, 
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = did_billing_rel.sell_billing_plan_id LIMIT 1) as vendor_rule,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = did_billing_rel.buy_billing_plan_id LIMIT 1) as client_rule,
(SELECT ip FROM resource_ip WHERE rs.resource_id = resource_ip.resource_id LIMIT 1) as vendor_ip
FROM resource
LEFT JOIN product_items_resource on product_items_resource.resource_id = resource.resource_id
LEFT JOIN product_items on product_items_resource.item_id = product_items.item_id
LEFT JOIN rate on rate.code = product_items.digits  
LEFT JOIN did_billing_rel ON cast(did_billing_rel.did as varchar(255)) = cast(rate.code as varchar(255))
LEFT JOIN resource rs on rate.rate_table_id = rs.rate_table_id
WHERE rate.did_type = 1 AND resource.client_id='$client_id'  LIMIT $pageSize OFFSET $offset
SQL;
        return $this->query($sql);
    }

    public function get_data_by_client_count($client_id){
        $sql = <<<SQL
SELECT count(*) total
FROM resource
LEFT JOIN product_items_resource on product_items_resource.resource_id = resource.resource_id
LEFT JOIN product_items on product_items_resource.item_id = product_items.item_id
LEFT JOIN rate on rate.code = product_items.digits  
LEFT JOIN did_billing_rel ON cast(did_billing_rel.did as varchar(255)) = cast(rate.code as varchar(255))
LEFT JOIN resource rs on rate.rate_table_id = rs.rate_table_id
WHERE rate.did_type = 1 AND resource.client_id='$client_id'
SQL;
        $data = $this->query($sql);
        return $data[0][0]['total'];
    }



    public function get_data_by_id($id)
    {
        $sql = <<<SQL
SELECT client_resource.alias, t1.id, t1.code, t1.code_name, t1.country, t1.egress_res_id as vendor_id, t1.ingress_res_id as client_id, vendor_client.name as vendor_name,
orig_client.name as client_name, t1.create_time, t1.update_at,
(SELECT ip FROM resource_ip WHERE resource_ip.resource_id = t1.egress_res_id LIMIT 1) as vendor_ip,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as vendor_rule,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.buy_billing_plan_id LIMIT 1) as client_rule,
t1.sell_billing_plan_id as vendor_billing_id,
t1.end_date as end_date,
t1.buy_billing_plan_id as client_billing_id,
t1.start_date as start_date
from
(
SELECT rate.code, did_billing_rel.*, rate.create_time,product_items.update_at, rate.did_type, rate.code_name, rate.country
from did_billing_rel 
INNER JOIN rate ON rate.rate_id = did_billing_rel.rate_id
LEFT JOIN product_items on rate.code = product_items.digits
-- left join product_items_resource ON product_items_resource.item_id = product_items.item_id 
WHERE rate.did_type = 1 AND did_billing_rel.id = {$id} order by did_billing_rel.did desc 
) as t1
LEFT JOIN client as vendor_client ON vendor_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.egress_res_id)
LEFT JOIN client as orig_client ON orig_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.ingress_res_id)
LEFT JOIN resource as client_resource ON resource_id = t1.ingress_res_id
SQL;
        return $this->query($sql);
    }

    public function get_ingress($ingress_id = '')
    {
        $data = array();
        if (!empty($ingress_id))
            $other_conditions =  " and rs.resource_id = $ingress_id";
        else
            $other_conditions = '';
        $sql = "select rs.resource_id, rs.alias from resource rs LEFT JOIN client on rs.alias = client.name where rs.ingress=true and rs.trunk_type2=1 and client.client_type = 0 $other_conditions  order by alias asc";
        $result = $this->query($sql);
        $data[0] = '';
        foreach ($result as $item)
            $data[$item[0]['resource_id']] = $item[0]['alias'];
        return $data;
    }

    public function get_egress($client_id = '')
    {
        $data = array(
            ''
        );
        if (!empty($client_id))
            $other_conditions =  " and client_id = $client_id";
        else
            $other_conditions = '';

        $linkedName = "_" . base64_encode('DID') . "_";

        $sql = <<<SQL
select resource.resource_id, resource.alias 
from resource 
LEFT JOIN client ON client.client_id = resource.client_id
where resource.egress=true and resource.trunk_type2=1  AND client.client_type = 1 AND resource.alias NOT LIKE '%{$linkedName}%' $other_conditions 
order by resource.alias asc;
SQL;

        $result = $this->query($sql);
        $data[0] = '';
        foreach ($result as $item)
            $data[$item[0]['resource_id']] = $item[0]['alias'];
        return $data;
    }

    public function get_carriers($client_type = 1)
    {
        $sql = "select distinct client.client_id as id, client.name from client inner join resource on client.client_id = resource.client_id where resource.egress = true AND client_type='$client_type' order by client.name";
        $result = $this->query($sql);
        $data[0] = '';
        foreach ($result as $item)
            $data[$item[0]['id']] = $item[0]['name'];
        return $data;
    }

    public function insert_to_repository($vendor_id,$number, $vendorBillingId)
    {
        if (!empty($vendorBillingId)) {
            $sql = <<<SQL
INSERT INTO rate (rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate,did_type,create_time,country,code_name,effective_date)
SELECT resource.rate_table_id,'$number',did_billing_plan.min_price,6,6,did_billing_plan.min_price,
did_billing_plan.min_price,1,current_timestamp(0),'','',current_timestamp(0)
FROM resource 
INNER JOIN did_billing_plan ON did_billing_plan.id = {$vendorBillingId} WHERE resource.resource_id = $vendor_id
ORDER BY resource.rate_table_id DESC LIMIT 1
returning rate_id;
SQL;
        } else {
            $sql = <<<SQL
INSERT INTO rate (rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate,did_type,create_time,country,code_name,effective_date)
SELECT resource.rate_table_id,'$number',0,6,6,0,
0,1,current_timestamp(0),'','',current_timestamp(0)
FROM resource 
WHERE resource.resource_id = $vendor_id
ORDER BY resource.rate_table_id DESC LIMIT 1
returning rate_id;
SQL;
        }
        return $this->query($sql);
    }


    public function get_product_id()
    {
        $sql = "select product_id from product where name = 'ORIGINATION_STATIC_ROUTE'";
        $result = $this->query($sql);
        if(!empty($result))
            return $result[0][0]['product_id'];
        else
        {
            $sql = "insert into product(name) values ('ORIGINATION_STATIC_ROUTE') returning product_id";
            $result = $this->query($sql);
            return $result[0][0]['product_id'];
        }
    }

    public function assign_did($client_id, $vendor_id, $rateId, $vendorBillingId, $clientBillingId, $mass = false, $id = null)
    {
        if ($id) {
            $did = $this->query("SELECT rate_id FROM did_billing_rel WHERE id = {$id}");
            $rateId = $did[0][0]['rate_id'];
        }
        $rate = $this->query("SELECT code FROM rate WHERE rate_id = {$rateId}");
        $number = $rate[0][0]['code'];

        if ($client_id) {
            if ($clientBillingId && $client_id) {
                $sql = "SELECT rate_table_id, name FROM did_billing_plan WHERE id={$clientBillingId}";
                $result = $this->query($sql);

                if ($result) {
                    $clientName = $this->query("SELECT alias FROM resource WHERE resource_id = {$client_id}");
                    $clientName = str_replace(' ', '_', $clientName[0][0]['alias']) . "_" . base64_encode('DID') . "_" . str_replace(' ', '_', $result[0][0]['name']);
                    $newClient = $this->query("SELECT resource_id FROM resource WHERE alias = '{$clientName}'");

                    if (empty($newClient)) {
                        $sql = <<<EOT
                                INSERT INTO resource (alias, client_id, media_type, capacity, trunk_type2, egress, rate_table_id, enough_balance, billing_rule, route_strategy_id, t38)
                                SELECT '{$clientName}', client_id, media_type, capacity, trunk_type2, egress, {$result[0][0]['rate_table_id']}, enough_balance, billing_rule, route_strategy_id,'true' FROM resource WHERE resource_id = {$client_id}
                                returning resource_id
EOT;
                        $newClient = $this->query($sql);
                        $hosts = $this->query("SELECT ip, port FROM resource_ip WHERE resource_id = {$client_id}");

                        foreach ($hosts as $host) {
                            $this->query("INSERT INTO resource_ip (resource_id, ip, port) VALUES ({$newClient[0][0]['resource_id']}, '{$host[0]['ip']}', {$host[0]['port']})");
                        }
                    }

                    if ($newClient) {
                        $newClient = $newClient[0][0]['resource_id'];
                    } else {
                        return false;
                    }
                }
            }
            $product_id = $this->get_product_id();
            $exist_sql = "SELECT count(*) as sum FROM product_items INNER JOIN product_items_resource ON product_items.item_id = product_items_resource.item_id
WHERE product_items_resource.resource_id = $client_id AND product_id = $product_id AND strategy = 1 AND digits = '$number';";
            $exist_count = $this->query($exist_sql);

            if (!$exist_count[0][0]['sum']) {
                $delete_sql = "DELETE FROM product_items where product_id = $product_id and digits = '{$number}'";
                $this->query($delete_sql);

                if ($mass === false) {
                    $this->begin();
                }
                $sql = "insert into product_items (product_id, digits, strategy, update_by) values ({$product_id} , '{$number}' , 1, '{$_SESSION['sst_user_name']}') returning item_id";
                $new_product_item = $this->query($sql);

                if ($new_product_item === false) {
                    return false;
                }
                $product_item_id = $new_product_item[0][0]['item_id'];
                $sql = "insert into product_items_resource(item_id, resource_id) values ({$product_item_id}, {$newClient})";
                $res = $this->query($sql);

                if ($res === false) {
                    if ($mass === false)
                        $this->rollback();
                    return false;
                }
                $sql = <<<SQL
INSERT INTO rate (rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate,did_type,create_time,country,code_name,effective_date)
SELECT resource.rate_table_id,'$number',did_billing_plan.min_price,6,6,did_billing_plan.min_price,
did_billing_plan.min_price,2,current_timestamp(0),'','',current_timestamp(0)
FROM resource LEFT JOIN did_billing_plan ON did_billing_plan.id = resource.billing_rule WHERE resource.resource_id = $newClient
returning rate_id;
SQL;
                $this->query($sql);

                if ($mass === false)
                    $this->commit();
            }
        }

        if ($vendorBillingId && $vendor_id) {
            $sql = "SELECT rate_table_id FROM did_billing_plan WHERE id={$vendorBillingId}";
            $result = $this->query($sql);

            if ($result) {
                $this->query("DELETE FROM resource_prefix WHERE rate_table_id = {$result[0][0]['rate_table_id']} AND resource_id = {$vendor_id}");

                $sql = "insert into resource_prefix (code, rate_table_id, resource_id, route_strategy_id) values ('{$number}', {$result[0][0]['rate_table_id']}, {$vendor_id}, (SELECT route_strategy_id FROM route_strategy where name = 'ORIGINATION_ROUTING_PLAN'))";
                $this->query($sql);
            }

            if ($id) {
                $this->query("UPDATE did_billing_rel SET did_billing_id = {$clientBillingId}, buy_billing_plan_id = {$clientBillingId}, sell_billing_plan_id = {$vendorBillingId}, ingress_res_id = {$newClient}, egress_res_id = {$vendor_id}, rate_id = {$rateId} WHERE id = {$id}");
            } else {
                $checkExists = $this->query("SELECT id, start_date FROM did_billing_rel WHERE did = '{$number}' ORDER BY id DESC LIMIT 1");

                if (!empty($checkExists)) {
                    if (strtotime($checkExists[0][0]['start_date']) >= strtotime(date('Y-m-d'))) {
                        $expiredDate = $checkExists[0][0]['start_date'];
                    } else {
                        $expiredDate = date('Y-m-d');
                    }
                    $startDateNewRecord = date('Y-m-d', strtotime($expiredDate . '+1 days'));

                    $this->query("UPDATE did_billing_rel SET end_date = '{$expiredDate}' WHERE id = {$checkExists[0][0]['id']}");

                    if ($newClient) {
                        $this->query("INSERT INTO did_billing_rel (did, did_billing_id, buy_billing_plan_id, sell_billing_plan_id, ingress_res_id, egress_res_id, start_date, rate_id) VALUES ('{$number}', {$clientBillingId}, {$clientBillingId}, {$vendorBillingId}, {$newClient}, {$vendor_id}, '{$startDateNewRecord}', {$rateId})");
                    } else {
                        $this->query("INSERT INTO did_billing_rel (did, sell_billing_plan_id, egress_res_id, start_date, rate_id) VALUES ('{$number}', {$vendorBillingId}, {$vendor_id}, '{$startDateNewRecord}', {$rateId})");
                    }
                } else {
                    $startDate = date('Y-m-d');

                    if ($newClient) {
                        $this->query("INSERT INTO did_billing_rel (did, did_billing_id, buy_billing_plan_id, sell_billing_plan_id, ingress_res_id, egress_res_id, start_date, rate_id) VALUES ('{$number}', {$clientBillingId}, {$clientBillingId}, {$vendorBillingId}, {$newClient}, {$vendor_id}, '{$startDate}', {$rateId})");
                    } else {
                        $this->query("INSERT INTO did_billing_rel (did, sell_billing_plan_id, egress_res_id, start_date, rate_id) VALUES ('{$number}', {$vendorBillingId}, {$vendor_id}, '{$startDate}', {$rateId})");
                    }
                }
            }
        }
//        $end_date = date("Y-m-d");
//        $start_date = date("Y-m-d");
//
//        if(isset($client_change_flg) && $client_change_flg){
//            // update end date for last assign and create new assign
//            $sql = "UPDATE did_billing_rel set end_date = '{$end_date}' WHERE did = '{$number}' AND end_date IS NULL";
//            $this->query($sql);
//            $start_date = date('Y-m-d', strtotime($start_date . '+1 days'));
//            $sql = "INSERT INTO did_billing_rel (did, did_billing_id, buy_billing_plan_id, sell_billing_plan_id, start_date) VALUES ('{$number}', {$clientBillingId}, {$clientBillingId}, {$vendorBillingId},'{$start_date}')";
//        } else {
//            // check if exists
//            $sql ="SELECT start_date FROM did_billing_rel WHERE did = '{$number}' AND end_date IS NULL";
//            $data = $this->query($sql);
//
//            if(!empty($data)){
//                if($data[0][0]['start_date']){
//                    $sql = "UPDATE did_billing_rel set did_billing_id = '{$clientBillingId}',buy_billing_plan_id = '{$clientBillingId}',sell_billing_plan_id = '{$vendorBillingId}' WHERE did = '{$number}' AND end_date IS NULL";
//                }else{
//                    $sql = "UPDATE did_billing_rel set did_billing_id = '{$clientBillingId}',buy_billing_plan_id = '{$clientBillingId}',sell_billing_plan_id = '{$vendorBillingId}',start_date = '{$start_date}' WHERE did = '{$number}' AND end_date IS NULL";
//                }
//            }else{
//                if ($client_id) {
//                    $sql = "INSERT INTO did_billing_rel (did, did_billing_id, buy_billing_plan_id, sell_billing_plan_id, start_date) VALUES ('{$number}', {$clientBillingId}, {$clientBillingId}, {$vendorBillingId}, '{$start_date}')";
//                } else {
//                    $sql = "INSERT INTO did_billing_rel (did, did_billing_id, buy_billing_plan_id, sell_billing_plan_id) VALUES ('{$number}', {$clientBillingId}, {$clientBillingId}, {$vendorBillingId})";
//                }
//            }
//        }
//        $this->query($sql);
        return true;
    }

    public function delete_by_vendor($vendor_id,$number)
    {
        $sql = "SELECT rate_table_id FROM resource_prefix WHERE resource_id = $vendor_id";
        $data = $this->query($sql);
        $rate_table_id = $data[0][0]['rate_table_id'];
//        $delete_sql = "DELETE FROM rate WHERE code = '$number' AND rate_table_id = $rate_table_id";
        $delete_sql = "DELETE FROM rate WHERE code = '$number'";
        return $this->query($delete_sql);
    }

    public function delete_by_client($client_id,$number)
    {
//        $sql = "SELECT rate_table_id FROM resource WHERE resource_id = $client_id;";
//        $data = $this->query($sql);
//        $rate_table_id = $data[0][0]['rate_table_id'];
        $sql = "DELETE FROM rate WHERE code = '$number' AND rate_table_id = (SELECT rate_table_id FROM resource WHERE resource_id = $client_id limit 1)";
        $flg = $this->query($sql);
        return $flg;
    }

    public function update_country_code_name($vendor_id,$client_id,$number,$country,$code_name,$vendor_changed,$client_changed)
    {
        $this->begin();
        if (!$vendor_changed)
        {
            $sql = "SELECT rate_table_id FROM resource_prefix WHERE resource_id = $vendor_id";
            $data = $this->query($sql);
            $rate_table_id = $data[0][0]['rate_table_id'];
            $sql = "UPDATE rate set code_name = '$code_name',country = '$country' WHERE rate_table_id = $rate_table_id AND code = '$number'";
            $flg = $this->query($sql);
            if ($flg === false)
            {
                $this->rollback();
                return false;
            }
        }
        if (!$client_changed)
        {
            $sql = "SELECT rate_table_id FROM resource WHERE resource_id = $client_id";
            $data = $this->query($sql);
            $rate_table_id = $data[0][0]['rate_table_id'];
            $sql = "UPDATE rate set code_name = '$code_name',country = '$country' WHERE rate_table_id = $rate_table_id AND code = '$number'";
            $flg = $this->query($sql);
            if ($flg === false)
            {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    public function delete_did($number)
    {
        $this->begin();
        $sql = "DELETE FROM rate WHERE did_type is not null and code = '$number'";
        $delete_rate = $this->query($sql);
        if ($delete_rate === false)
        {
            $this->rollback();
            return false;
        }
        $product_id = $this->get_product_id();
        $delete_sql = "DELETE FROM product_items where product_id = $product_id and digits = '{$number}'";
        $delete_items = $this->query($delete_sql);
        if ($delete_items === false)
        {
            $this->rollback();
            return false;
        }

        $delete_sql = "DELETE FROM did_billing_rel where did = '$number'";
        $this->query($delete_sql);

        $this->commit();
        return true;
    }

    public function delete_did_by_id($id)
    {
        $did = $this->query("SELECT * FROM did_billing_rel WHERE id = {$id}");

        $this->begin();
        $sql = "DELETE FROM rate WHERE did_type is not null and rate_id = {$did[0][0]['rate_id']}";
        $delete_rate = $this->query($sql);
        if ($delete_rate === false)
        {
            $this->rollback();
            return false;
        }

        $delete_sql = "DELETE FROM product_items_resource where resource_id = {$did[0][0]['ingress_res_id']}";
        $delete_items = $this->query($delete_sql);
        if ($delete_items === false)
        {
            $this->rollback();
            return false;
        }

        $delete_sql = "DELETE FROM resource_ip where resource_id = {$did[0][0]['ingress_res_id']}";
        $delete_items = $this->query($delete_sql);
        if ($delete_items === false)
        {
            $this->rollback();
            return false;
        }

        $delete_sql = "DELETE FROM resource where resource_id = {$did[0][0]['ingress_res_id']}";
        $delete_items = $this->query($delete_sql);
        if ($delete_items === false)
        {
            $this->rollback();
            return false;
        }

        $delete_sql = "DELETE FROM did_billing_rel where id = {$id}";
        $this->query($delete_sql);

        $this->commit();
        return true;

    }

    public function multiple_delete($selected_arr)
    {
        $result = true;

        foreach ($selected_arr as $selected)
        {
            $result = $this->delete_did_by_id($selected);

            if (!$result) {
                break;
            }
        }
        return $result;
    }


    public function check_exist_number($number)
    {
        $sql ="SELECT count(*) as sum FROM rate WHERE code = '$number' AND did_type = 1";
        $data = $this->query($sql);
        return $data[0][0]['sum'];
    }

    public function mass_assign($selected_arr,$client_id, $clientBillingId)
    {
        $rate_delete_arr = array();
        $items_delete_arr = array();

        foreach ($selected_arr as $selected)
        {
            if (!is_numeric($selected))
                continue;
            $rate_delete_arr[] = "code = '$selected'";
            $items_delete_arr[] = "digits = '$selected'";
        }

        if (empty($rate_delete_arr))
            return false;
//        $this->begin();
        $rate_delete_sql = implode(" OR ",$rate_delete_arr);
        $sql = "DELETE FROM rate WHERE did_type = 2 and ($rate_delete_sql)";
        $delete_rate = $this->query($sql);
        if ($delete_rate === false)
            return false;
        $assigned = true;
        Configure::write('debug', 2);
        foreach ($selected_arr as $selected)
        {
            if (!is_numeric($selected))
                continue;

            $sql = <<<SQL
SELECT t1.code,t1.code_name,t1.country,t1.resource_id as vendor_id,t1.create_time,t1.update_at, 
(SELECT ip FROM resource_ip WHERE resource_ip.resource_id = t1.resource_id LIMIT 1) as vendor_ip,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = did_billing_rel.sell_billing_plan_id LIMIT 1) as vendor_rule,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = did_billing_rel.buy_billing_plan_id LIMIT 1) as client_rule
from
(
SELECT rate.code, (SELECT resource.resource_id FROM resource WHERE resource.rate_table_id = rate.rate_table_id order by resource_id desc limit 1) as resource_id,
rate.create_time,product_items.update_at,rate.did_type,rate.code_name,rate.country 
from rate 
LEFT JOIN product_items on rate.code = product_items.digits WHERE rate.did_type = 1 order by code desc 
) as t1
LEFT JOIN did_billing_rel ON cast(did_billing_rel.did as varchar(255)) = cast(t1.code as varchar(255))
where t1.code = '$selected';
SQL;

            $data = $this->query($sql);
            $vendorId = $data[0][0]['vendor_id'];

            $sql = "select did_billing_id FROM did_billing_rel where did = '$selected' limit 1";
            $data = $this->query($sql);

            $vendorBillingId = $data[0][0]['did_billing_id'];
            $flg = $this->assign_did($client_id, $vendorId, $selected, $vendorBillingId, $clientBillingId, true);

            if ($flg === false)
            {
                $assigned = false;
                $this->rollback();
                break;
            }

        }
//        $this->commit();
        return $assigned;
    }

    /**
     * Warning: dangerous!
     */
    public final function deleteAllDidInfo()
    {
        $data = $this->query("select rs.resource_id, rs.rate_table_id, rs.alias, client.client_id from resource rs LEFT JOIN client on rs.alias = client.name where (rs.ingress=true or rs.egress=true) and rs.trunk_type2=1");

        foreach ($data as $item) {
            $rateTableId = $item[0]['rate_table_id'];
            $resourceId = $item[0]['resource_id'];
            $clientId = $item[0]['client_id'];

            $this->query("DELETE FROM rate WHERE rate_table_id = {$rateTableId}");
            $this->query("DELETE FROM rate_table WHERE rate_table_id = {$rateTableId}");
            $this->query("DELETE FROM resource WHERE resource_id = {$resourceId}");
            $this->query("DELETE FROM client WHERE client_id = {$clientId}");
        }

        $this->query("DELETE FROM did_billing_rel WHERE 1=1");
    }

    public function deleteByResourceId($id)
    {
        $data = $this->query("select rs.resource_id, rs.rate_table_id, rs.alias, client.client_id from resource rs LEFT JOIN client on rs.client_id = client.client_id where (rs.ingress=true or rs.egress=true) and rs.resource_id = {$id}");
        $rateTableId = $data[0][0]['rate_table_id'];
        $resourceId = $data[0][0]['resource_id'];
        $clientId = $data[0][0]['client_id'];

        if ($rateTableId) {
            $result = $this->query("DELETE FROM rate WHERE rate_table_id = {$rateTableId}");
            if ($result === false) {
                $this->rollback();
                return false;
            }

            $result = $this->query("DELETE FROM rate_table WHERE rate_table_id = {$rateTableId}");
            if ($result === false) {
                $this->rollback();
                return false;
            }
        }

        $result = $this->query("DELETE FROM resource WHERE resource_id = {$resourceId}");
        if ($result === false) {
            $this->rollback();
            return false;
        }

        $result = $this->query("DELETE FROM resource_ip WHERE resource_id = {$resourceId}");
        if ($result === false) {
            $this->rollback();
            return false;
        }

        if ($clientId) {
            $result = $this->query("DELETE FROM client WHERE client_id = {$clientId}");
            if ($result === false) {
                $this->rollback();
                return false;
            }
        }

        $result = $this->query("DELETE FROM users WHERE name = '{$data[0][0]['alias']}'");
        if ($result === false) {
            $this->rollback();
            return false;
        }

        return true;
    }

    public function deleteOnlyVendorByResourceId($id)
    {
        $data = $this->query("select client.client_id from resource rs LEFT JOIN client on rs.alias = client.name where (rs.ingress=true or rs.egress=true) and rs.trunk_type2=1 and rs.resource_id = {$id}");

        $clientId = $data[0][0]['client_id'];
        $result = $this->query("DELETE FROM client WHERE client_id = {$clientId}");
        if ($result === false) {
            $this->rollback();
            return false;
        }

        return true;
    }

    public function exportDid($selected, $file, $type)
    {
        $ids = '\'' . implode('\',\'', $selected) . '\'';

        if ($type == 1) {
            $sql = "select rate.code as DID from resource  left join rate on rate.rate_table_id = resource.rate_table_id  where resource.resource_id in ({$ids})";
        } else {
            $sql = "select rate.code as DID from resource left join rate on rate.rate_table_id = resource.rate_table_id inner join did_billing_rel on cast(did_billing_rel.did as varchar(255)) = cast(rate.code as varchar(255)) where resource.resource_id in ({$ids})";
        }
        $sql = "\COPY ($sql) TO '{$file}' CSV HEADER";

        $this->_get_psql_cmd($sql);
    }

    public function getUnassignedDids($prefix, $country)
    {
        $prefixes = ['1800', '1888', '1877', '1866', '1855', '1844'];
        $prefixes = implode('|', $prefixes);

        $where = empty($prefix) ? " cast(t1.code as varchar(255)) NOT SIMILAR TO '({$prefixes})%'" : " cast(t1.code as varchar(255)) like '{$prefix}%'";

        if (!empty($country)) {
            $where .= " AND code.country like '%{$country}%'";
        }

        $sql = <<<SQL
SELECT client_resource.alias, t1.id, t1.code, t1.code_name, code.country, code.state, t1.egress_res_id as vendor_id, t1.ingress_res_id as client_id, vendor_client.name as vendor_name,
orig_client.name as client_name, t1.create_time, t1.update_at, t1.jur_type,
(SELECT ip FROM resource_ip WHERE resource_ip.resource_id = t1.egress_res_id LIMIT 1) as vendor_ip,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as vendor_rule,
(SELECT did_billing_plan.did_price FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as one_time_fee,
(SELECT did_billing_plan.monthly_charge FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as monthly_fee,
(SELECT did_billing_plan.min_price FROM did_billing_plan WHERE did_billing_plan.id = t1.sell_billing_plan_id LIMIT 1) as per_min_fee,
(SELECT did_billing_plan.name FROM did_billing_plan WHERE did_billing_plan.id = t1.buy_billing_plan_id LIMIT 1) as client_rule,
t1.sell_billing_plan_id as vendor_billing_id,
t1.end_date as end_date,
t1.buy_billing_plan_id as client_billing_id,
t1.start_date as start_date
from
(
SELECT rate.code, did_billing_rel.*, rate.create_time,product_items.update_at, rate.did_type, rate.code_name, rate.country, rate_table.jur_type
from did_billing_rel 
INNER JOIN rate ON rate.rate_id = did_billing_rel.rate_id
LEFT JOIN product_items on rate.code = product_items.digits
LEFT JOIN rate_table ON rate_table.rate_table_id = rate.rate_table_id
WHERE rate.did_type = 1 order by did_billing_rel.did desc
) as t1
LEFT JOIN client as vendor_client ON vendor_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.egress_res_id)
LEFT JOIN client as orig_client ON orig_client.client_id = (SELECT client_id FROM resource WHERE resource_id = t1.ingress_res_id)
LEFT JOIN resource as client_resource ON resource_id = t1.ingress_res_id
LEFT JOIN code ON code.code = t1.code
WHERE t1.ingress_res_id is null AND $where
SQL;
//        die(var_dump($sql));
        return $this->query($sql);
    }

    public function getRelData($did)
    {
        $data = $this->query("SELECT * FROM did_billing_rel WHERE did = '{$did}' LIMIT 1");

        return $data;
    }

}
