<?php
namespace App\Services;

use App\Model\Token;

class ERC20TokenApi {
	private $chainNetwork = '';
	private $headerKey = '';
	private $nodeUrl = '';
    private $logger;
    private $settings;
    private $contractAddress;
    private $decimalValue;
    private $gasLimit;
    private $prevBlockCount;
    private $networkType;
    private $adminAddress;
    private $chainId;

    public function setCurrentChain($chainNetwork)
    {
        $this->chainNetwork = $chainNetwork->chain_links ? json_decode($chainNetwork->chain_links)[0] : '';
        $this->contractAddress = $chainNetwork->contract_address ?? '';
        $this->decimalValue = isset($chainNetwork->decimals) ? intval($chainNetwork->decimals) : 18;
        $this->gasLimit = isset($chainNetwork->gas_limit) ? intval($chainNetwork->gas_limit) : 0;
        $this->prevBlockCount = isset($chainNetwork->previous_block_count) ? intval($chainNetwork->previous_block_count) : 100;
        $this->adminAddress = $chainNetwork->address ?? '';
        $this->chainId = $chainNetwork->chain_id ?? 0;
        $this->networkType = isset($chainNetwork->network_type) ? intval($chainNetwork->network_type) : ERC20_TOKEN;
    }
    public function __construct()
    {
        $this->logger = new Logger();
        $this->settings = allsetting();
        $this->chainNetwork = $this->settings['chain_link'] ?? '';
        $this->headerKey = env('HEADER_API_KEY') ?? '32c412e1f281fea2c93fd972a212040b692b9bdd';
        $this->nodeUrl = env('ERC20_NODE_URL') ?? 'http://localhost:4000/';
//        $this->contractAddress = $this->settings['contract_address'] ?? '';
//        $this->decimalValue = isset($this->settings['contract_decimal']) ? intval($this->settings['contract_decimal']) : 18;
//        $this->gasLimit = isset($this->settings['gas_limit']) ? intval($this->settings['gas_limit']) : 0;
//        $this->prevBlockCount = isset($this->settings['previous_block_count']) ? intval($this->settings['previous_block_count']) : 100;
//        $this->adminAddress = $this->settings['wallet_address'] ?? '';
//        $this->chainId = $this->settings['chain_id'] ?? 0;
//        $this->networkType = isset($this->settings['network_type']) ? intval($this->settings['network_type']) : ERC20_TOKEN;

    }

    public function apiCall($endPoint,$requestData = null,$type)
    {
        try {
            $header = array();
//        $header[] = 'Content-length: 0';
            $header[] = 'Content-type: application/json; charset=utf-8';
            $header[] = 'Accept: application/json';
            $header[] = 'headerkeys:'.$this->headerKey;
            $header[] = 'chainlinks:'.$this->chainNetwork;
            $header[] = 'networkType:' . $this->networkType;

            $this->logger->log('node apiCall1', $this->nodeUrl);

            $this->logger->log('node apiCall', $this->nodeUrl.$endPoint);

            $node_url = $this->nodeUrl.$endPoint;
            $postData =  !empty($requestData) ? json_encode($requestData) : "";

            $ch = curl_init($node_url);
            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $node_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            //create the multiple cURL handle
            $mh = curl_multi_init();

            curl_multi_add_handle($mh,$ch);

            //execute the multi handle
            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $status == CURLM_OK);

            //close the handles
            curl_multi_remove_handle($mh, $ch);
            curl_multi_close($mh);

            // all of our requests are done, we can now access the results
            $responseData = curl_multi_getcontent($ch);

            if ($responseData != "") {
                $result = json_decode($responseData);
                if ($result->status == false) {
                    if ($result->message != "nodatafound") {
                        storeException('ERC20TokenApi result err', $result->message);
                    }
                }
                $response = ['success' => $result->status, 'message' => $result->message, 'data' => $result->data ?? '' ];
            } else {
                $response = ['success' => false, 'message' => __('No api response'), 'data' =>'' ];
            }
        } catch (\Exception $e) {
            $this->logger->log('node apiCall', $e->getMessage());
            $response = ['success' => false, 'message' => __('Node api call failed'), 'data' =>'' ];
        }
        return $response;
    }

    //get wallet balance
    /*[▼
    "success" => true
    "message" => "process successfully"
    "data" => { ▼
        "net_balance": "1.472266317"
        "token_balance": 0
    }
    ]*/
    public function checkWalletBalance($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress,
                'admin_address' => $this->adminAddress,
            ]);
            $response = $this->apiCall('check-wallet-balance',$data,'POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall checkWalletBalance ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // create new wallet
    /*[▼
    "success" => true
    "message" => "Wallet created successfully"
    "data" => { ▼
        "address": "0xda786857cDaA280d6f168F2E117fA5017a4Ec179"
        "privateKey": "0x8b814548f778ee84cd8ebd6a54njk534n5kj2904ed8ab6c923ed1294fb5dd"
    }
    ]*/
    public function createNewWallet()
    {
        try {
            $response = $this->apiCall('create-wallet','','POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall createNewWallet ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    //check gas fess
    /*[▼
    "success" => true
    "message" => "Get Estimate gas successfully"
    "data" => {
        "gasLimit": 216200
        "amount": "3000000"
        "tx": {#1781 ▶}
        "gasPrice": "0.00000001"
        "estimateGasFees": 0.00037617
    }
    ]*/
    public function checkEstimateGas($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress,
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue,
                'chain_id' => $this->chainId,
            ]);

            $response = $this->apiCall('check-estimate-gas',$data,'POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall checkEstimateGas ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // send custom token
    /*[▼
    "success" => true
    "message" => "Token sent successfully"
    "data" => {
        "hash": "0x2b55fe016b57203db5841cb534a4a4ff96d9a771d5fcd602cc75428f38fcddd9"
        "used_gas": 0.00037617
    "tx": {
        "blockHash": "0x2f8838e95b9c2f2b8b9a00cc82c881fd2ba15876c3f633ac6a6f6d4023edca4f"
        "blockNumber": 16178983
        "contractAddress": null
        "cumulativeGasUsed": 1882650
        "from": "0xf2df582ab8ba0c7e57e897ca3371aabb68648ca8"
        "gasUsed": 37617
        "logs": array:1 [▶]
        "logsBloom": "0x00000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000080000000000000000000000000000000000000008000000 ▶"
        "status": true
        "to": "0x2752eee959596ced6cfea51862ca9f6cf6e46745"
        "transactionHash": "0x2b55fe016b57203db5841cb534a4a4ff96d9a771d5fcd602cc75428f38fcddd9"
        "transactionIndex": 11
        "type": "0x0"
     }
    }
    ]*/
    public function sendCustomToken($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'contract_address' => $this->contractAddress,
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue,
                'chain_id' => $this->chainId,
            ]);

            $response = $this->apiCall('send-token',$data,'POST');
            $this->logger->log('apiCall sendCustomToken ', json_encode($response));
        } catch (\Exception $e) {
            $this->logger->log('apiCall sendCustomToken ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // send eth or bnb
    /*[▼
    "success" => true
    "message" => "Coin sent successfully"
    "data" => { ▼
        "hash": "0x9bc456e9184ada3ad74ec3124b43020059a7732d52e510d7a1058c2acc87d2b2"
    }
    ]*/
    public function sendEth($requestData)
    {
        try {
            $data = array_merge($requestData,[
                'gas_limit' => $this->gasLimit,
                'decimal_value' => $this->decimalValue,
                'chain_id' => $this->chainId,
            ]);

            $response = $this->apiCall('send-eth',$data,'POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall sendEth ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // get transaction data
    /*[▼
    "success" => true
    "message" => "get hash"
    "data" => {▼
        "hash": { ▼
            "blockHash": "0x705c17a4b8ca70ed2816cd9fb2c860224c40e6d3b72f6a0a217ed3b35140d6d3"
            "blockNumber": 16164874
            "contractAddress": null
            "cumulativeGasUsed": 8174988
            "from": "0xf2df582ab8ba0c7e57e897ca3371aabb68648ca8"
            "gasUsed": 37617
            "logs": array:1 [▶]
            "logsBloom": "0x00000000000000000000000000000000000000000000000000001000000000000000000000000000000000000000000000000000000000080000000000000000000000000000000000000008000000 ▶"
            "status": true
            "to": "0x2752eee959596ced6cfea51862ca9f6cf6e46745"
            "transactionHash": "0x634034c8d7ab3eedf941a2cb961e89da59f0c75950e4919e8959de1e7b9a1730"
            "transactionIndex": 13
            "type": "0x0"
        }
    "gas_used": 0.00037617
      }
    ]*/
    public function getTransactionData($requestData)
    {
        try {
            $response = $this->apiCall('get-transaction-data',$requestData,'POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall getTransactionData ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>'' ];
        }
        return $response;
    }

    // get transfer event for contract address
    /*[▼
    "success" => true
    "message" => "found block details"
    "data" => {▼
        "result": array:2 [▼
            0 => {▼
                "event": "Transfer"
                "signature": "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef"
                "contract_address": "0x2752eEE959596cED6CfeA51862CA9F6cf6e46745"
                "tx_hash": "0x9d2cbaa58801119497f52defaf2680de9fa2ac5ea065f1425a2e89781ac9c8ee"
                "block_hash": "0x0d859cf36a3a8d04b1aa11a3d09c715e9dda790f7da7332b731986cb1a00430f"
                "from_address": "0xf2DF582ab8bA0C7E57e897Ca3371AAbB68648CA8"
                "to_address": "0xe0eAf3B1eBc93e6f85F94c614122E3C15dADCFf9"
                "amount": "1"
            }
            1 => {#1836 ▶}
                ]
            }
    ]*/
    public function getContractTransferEvent()
    {
        try {
            $requestData = [
                "contract_address" => $this->contractAddress,
                "number_of_previous_block" => $this->prevBlockCount,
                "decimal_value" => $this->decimalValue,
                'admin_address' => $this->adminAddress,
            ];
            $response = $this->apiCall('get-transfer-event',$requestData,'POST');
        } catch (\Exception $e) {
            $this->logger->log('apiCall getContractTransferEvent ', $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage(), 'data' =>'' ];
        }
        return $response;
    }

    public function checkContractDetails($requestData)
    {
        try {
            storeException('apiCall checkWalletBalance req data', $requestData);
            $response = $this->apiCall('get-contract-details', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall checkContractDetails ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get trx account details by address
    /*$requestData = [
        'address' => "" required , trx address
        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function getTrxAccount($requestData)
    {
        try {
            $response = $this->apiCall('get-trx-account', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall getTrxAccount ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get trx address by private key
    /*$requestData = [
        'key' => "" required , trx private key
        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function getTrxAddress($requestData)
    {
        try {
            $response = $this->apiCall('get-trx-address', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall getTrxAddress ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get trx address by private key
    /*$requestData = [
        'address' => "" required , trx address
        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function checkTrxAddress($requestData)
    {
        try {
            $response = $this->apiCall('check-trx-address', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall checkTrxAddress ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get trx confirmed transaction by using transaction hash
    /*$requestData = [
        'transaction_hash' => "" required , trx transaction hash
        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function getTrxConfirmedTransaction($requestData)
    {
        try {
            $response = $this->apiCall('get-trx-confirmed-transaction', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall getTrxConfirmedTransaction ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get trc20 token transfer event
    /*$requestData = [

        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function getTrc20TransferEventWatch($requestData)
    {
        try {
            $data = array_merge($requestData, [
                'contract_address' => $this->contractAddress,
                'admin_address' => $this->adminAddress,
            ]);
            $response = $this->apiCall('get-trc-transaction-event-watch', $data, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall getTrc20TransferEvent ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

    //get address by private key
    /*$requestData = [
        'contracts' => "" required ,
        ];*/
    /*[▼
        "success" => true
        "message" => "process successfully"
        "data" => { ▼

        }
        ]*/
    public function getAddressFromPK($requestData)
    {
        try {
            $response = $this->apiCall('get-address-by-pk', $requestData, 'POST');
        } catch (\Exception $e) {
            storeException('apiCall getAddressFromPK ', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => ''];
        }
        return $response;
    }

};



