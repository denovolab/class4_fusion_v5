<?php

class VendorsController extends ApiAppController
{

    var $name = 'Vendors';
    var $uses = array('Client', 'User', 'prresource.Gatewaygroup', 'Rate', 'did.DidBillingPlan');
    var $components = array('RequestHandler');
    var $helpers = array('javascript', 'html', 'Common');
    
    public function beforeFilter() {
            Configure::write("debug", 0);
//            $this->autoLayout = false;
//            $this->autoRender = false;
            
            return true;
    }

    
    public function modify($client_id)
    {
        $client = array();
        $clientInfo = $this->Client->findByClientId($client_id);
        $client['client_id'] = $clientInfo['Client']['client_id'];
        $client['name'] = $clientInfo['Client']['name'];
        $userInfo = $this->User->findByClientId($client_id);
        $client['login_username'] = $userInfo['User']['name'];
        $resourceInfo = $this->Gatewaygroup->findByClientId($client_id);
        $client['call_limit'] = $resourceInfo['Gatewaygroup']['capacity'];
        $client['media_type'] = $resourceInfo['Gatewaygroup']['media_type'];
        $client['t_38'] = $resourceInfo['Gatewaygroup']['t38'];
        $client['rfc2833'] = $resourceInfo['Gatewaygroup']['rfc_2833'];
        $client['billing_rule'] = $resourceInfo['Gatewaygroup']['billing_rule'];
        $client['resource_ips'] = array();
        
        
        if ($this->RequestHandler->isPost()) {
            $name = $_POST['name'];
            $login_username = $_POST['login_username'];
            $login_password = $_POST['login_password'];
            $ip_addresses   = $_POST['ip_addresses'];
            $call_limit     = $_POST['call_limit'];
            $media_type     = $_POST['media_type'];
            $t_38           = isset($_POST['t_38']) and $_POST['t_38'] == '1'? TRUE : FALSE;
            $rfc2833           = isset($_POST['rfc2833']) and $_POST['rfc2833'] == '1' ? TRUE : FALSE;
            $billing_rule = $_POST['pricing_rule'];
            
            $clientInfo['Client']['name'] = $name;
            $clientInfo['Client']['login'] = $login_username;
            $clientInfo['Client']['update_at'] = date("Y-m-d H:i:s");
            $clientInfo['Client']['update_by'] = $_SESSION['sst_user_name'];
            if ($login_password != $userInfo['User']['password'])
                 $clientInfo['Client']['password'] = $login_password;
            $this->Client->save($clientInfo);
            
            $userInfo['User']['name'] = $login_username;
            if ($login_password != $userInfo['User']['password'])
                $userInfo['User']['password'] = md5($login_password);
            $this->User->save($userInfo);
            
            $resourceInfo['Gatewaygroup']['alias'] = $name;
            $resourceInfo['Gatewaygroup']['media_type'] = $media_type;
            $resourceInfo['Gatewaygroup']['capacity'] = $call_limit;
            $resourceInfo['Gatewaygroup']['t38'] = $t_38;
            $resourceInfo['Gatewaygroup']['rfc_2833'] = $rfc2833;
            $resourceInfo['Gatewaygroup']['billing_rule'] = $billing_rule;
            $this->Gatewaygroup->save($resourceInfo);
            
            $resource_id = $resourceInfo['Gatewaygroup']['resource_id'];
            $sql = "delete from resource_ip where resource_id = {$resource_id}";
            $this->Gatewaygroup->query($sql);
            
            foreach ($ip_addresses as $ip_address) {
               if (!empty($ip_address)) {
                   $sql = "insert into resource_ip(resource_id, ip) values($resource_id, '{$ip_address}')";
                   $this->Gatewaygroup->query($sql);
               }
           }
           
           header('HTTP/1.1 200 OK');
            $response = array(
                'status' => 200, 'vendor_id' => $client_id
            );
            $this->set('data', $response);
        }
        
//        $resource_ips_result = $this->Client->query("select ip from resource_ip where resource_id = {$resourceInfo['Gatewaygroup']['resource_id']}");
//        foreach ($resource_ips_result as $resource_ip) {
//            array_push($client['resource_ips'], $resource_ip[0]['ip']);
//        }
//        $this->set("routing_rules", $this->Gatewaygroup->getBillingRules());
//        $this->set('client', $client);
    }
    
    public function add()
    {
        if ($this->RequestHandler->isPost()) 
        {
            $name = $_POST['name'];
            $login_username = $_POST['login_username'];
            $login_password = $_POST['login_password'];
            $ip_addresses   = $_POST['ip_addresses'];
            $call_limit     = $_POST['call_limit'];
            $media_type     = $_POST['media_type'];
            $t_38           = isset($_POST['t_38']) and $_POST['t_38'] == '1'? TRUE : FALSE;
            $rfc2833           = isset($_POST['rfc2833']) and $_POST['rfc2833'] == '1' ? TRUE : FALSE;
            $billing_rule = $_POST['pricing_rule'];
            
            $sql = "SELECT currency_id FROM currency WHERE code = 'USA' lIMIT 1";
            $currency_result = $this->Client->query($sql);

            if (empty($currency_result))
            {
                
                $currency_result = $this->Client->query("INSERT INTO currency (code) VALUES ('USA') returning currency_id");
            }
                    
            $client = array(
                'Client' => array(
                    'name' => $name,
                    'mode' => 2,
                    'currency_id' => $currency_result[0][0]['currency_id'],
                    'is_panelaccess' => true,
                    'client_type' => 0, 
                    'login' => $login_username,
                    'password' => $login_password,
                    'update_by' => $_SESSION['sst_user_name'],
                    'enough_balance' => true,
                )
            );
            
            // 检测是否存在相同client
            
           $this->Client->save($client);
           $client_id = $this->Client->getLastInsertID();
           
           // 创建Balance
           $sql = "insert into client_balance_operation_action(client_id, balance) values ($client_id, 1)";
           $this->Client->query($sql);
           
           $user_password = md5($login_password);
//           $user = array(
//               'User' => array(
//                   'name' => $login_username,
//                   'password' => md5($login_password),
//                   'client_id' => $client_id,
//                   'user_type' => 3,
//               )
//           );
           $sql = "insert into users (name,password,client_id,user_type) values('$login_username','$user_password','$client_id',3)";
           $this->User->query($sql,false);
           // 创建 Ingress
           
           $ingress = array(
               'Gatewaygroup' => array(
                   'alias' => $name,
                   'client_id' => $client_id,
                   'media_type' => $media_type,
                   'capacity' => $call_limit,
                   't38' => $t_38,
                   'rfc_2833' => $rfc2833,
                   'trunk_type2' => 1,
                   'ingress' => true,
                   'billing_rule' => $billing_rule,
                   'enough_balance' => true,
               )
           );
           
           $rate_table_id = $this->Rate->create_ratetable($name, 'NULL', $currency_result[0][0]['currency_id'], 0, true, 2);
           
           
           $this->Gatewaygroup->save($ingress);
           $resource_id = $this->Gatewaygroup->getLastInsertID();
           
           foreach ($ip_addresses as $ip_address) {
               if (!empty($ip_address)) {
                   $sql = "insert into resource_ip(resource_id, ip) values($resource_id, '{$ip_address}')";
                   $this->Gatewaygroup->query($sql);
               }
           }
           
           $sql = "SELECT route_strategy_id from route_strategy where name = 'ORIGINATION_ROUTING_PLAN'";
           $result = $this->Gatewaygroup->query($sql);
           if(empty($result))
            {
                $sql = "insert into route_strategy (name) values ('ORIGINATION_ROUTING_PLAN') returning route_strategy_id";
                $result = $this->Gatewaygroup->query($sql);

                $sql = "select product_id from product where name = 'ORIGINATION_STATIC_ROUTE'";
                $result_static = $this->Gatewaygroup->query($sql);
                if(empty($result_static))
                {
                    $sql = "insert into product(name) values ('ORIGINATION_STATIC_ROUTE') returning product_id";
                    $result_static = $this->Gatewaygroup->query($sql);
                }
                $sql = "insert into route(static_route_id, route_type, route_strategy_id) values({$result_static[0][0]['product_id']}, 2, {$result[0][0]['route_strategy_id']});";
                $this->Gatewaygroup->query($sql);
            }
            $sql = "insert into resource_prefix (rate_table_id, route_strategy_id, resource_id) 
                            values ({$rate_table_id},{$result[0][0]['route_strategy_id']}, {$resource_id})";
                            
           $this->Gatewaygroup->query($sql);
           
           
           $billing_rule = $this->DidBillingPlan->findById($billing_rule);
            $min_price = $billing_rule['DidBillingPlan']['min_price'];
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '0', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '1', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '2', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '3', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '4', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '5', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '6', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '7', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '8', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
            $sql = "INSERT INTO rate(rate_table_id, code, rate, min_time, interval, intra_rate, inter_rate) VALUES ($rate_table_id, '9', $min_price, 6, 6, $min_price, $min_price)";
            $this->DidBillingPlan->query($sql);
           
           header('HTTP/1.1 200 OK');
            $response = array(
                'status' => 200, 'vendor_id' => $client_id
            );
            $this->set('data', $response);
           
        }
//        $this->set("routing_rules", $this->Gatewaygroup->getBillingRules());
    }
    
    public function disable()
    {
        $id = $this->params['pass'][0];
        $mesg_info = $this->Client->query("select name from client where client_id = {$id}");
        $this->Client->query("update client set  status=false where  client_id= $id;");
        $this->Client->query("update resource set  active=false where  client_id= $id;");
        $this->Client->create_json_array('', 201,  __('The Vendor [%s] is disabled successfully!', true, $mesg_info[0][0]['name']));
        $this->Session->write("m", Client::set_validator());
        $this->redirect('/did/vendors/index');
    }
    
    public function enable()
    {
        $id = $this->params['pass'][0];
        $mesg_info = $this->Client->query("select name from client where client_id = {$id}");
        $this->Client->active($id);
        $this->Client->query("update resource set  active=true where  client_id= $id;");
        $this->Client->create_json_array('', 201, __('The Vendor [%s] is enabled successfully!', true, $mesg_info[0][0]['name']));
        $this->Session->write("m", Client::set_validator());
        $this->redirect('/did/vendors/index');;
    }
    
    public function remove() 
    {
        if ($this->RequestHandler->isPost()) 
        {
            $client_id = $_POST['vendor_id'];
            $clientInfo = $this->Client->findByClientId($client_id);
            $this->Client->query("delete from users_limit where client_id = {$client_id}");
            $this->Client->del($client_id, 'false');
            
            header('HTTP/1.1 200 OK');
            $response = array(
                'status' => 200, 'vendor_id' => $client_id
            );
            $this->set('data', $response);
        }
    }
    
}