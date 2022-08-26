<?php

namespace UrbanBrussels\NovaApi;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RestrictedData
{
    private NovaConnection $nova_connection;

    public function __construct(NovaConnection $nova_connection)
    {
        $this->nova_connection = $nova_connection;
    }

    public function listDocumentsFromReferences(array $references, string $type = "ID"): array
    {
        [$content, $response] = $this->queryDocumentsFromReferences($references, $type);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode === 200 && $content !== "null" && !is_null($content)) { // New API: check is_null
                $content = $response->toArray();
            }

        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['publications'] ?? [];
    }

    public function statusDocumentsFromReferences(array $references, string $type = "ID"): int
    {
        [$content, $response] = $this->queryDocumentsFromReferences($references, $type);

        return $response->getStatusCode();
    }

    public function getCharges(string $uuid, string $type = "UUID"): array
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $this->nova_connection->endpoint.'api/nova-api/graph/1.0.0/graphql', [
            'auth_bearer' => $this->nova_connection->token,
            'headers' => [
                'Content-Type' => 'application/json',
            ],

            'body' => '{
 "query": "query ($arg:IdentifiersInput!) { Case_Details (identifiers:$arg) { urbanCharge { procedure {urbanCharge,cashCharge,natureCharge,imposedCharge,bankGuaranteeRequested}, generatorFact{cobat,governmentOrder{office{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},productionActivity{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},parliamentAndService{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},commercialParking{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},shop{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},wholesaler{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},hotel{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal},housing{active,squareMeter,squareMeterPrice,squareMeterDiscount,squareMeterTheoreticalTotal}},ppas,ppasDetail,ppasTotal,municipalityRule,municipalityRuleDetail,municipalityRuleTotal,subdivisionPermit,subdivisionPermitDetail,subdivisionPermitTotal other,otherDetail,otherTotal,reductionMotivation,reductionMotivationDetail,reductionMotivationTotal,commentDetail,linkedCaseDetail}, natureAndAssignment{nature{cash,nature,freeGrant},assignment{housing,housingTotal,supervisedHousing,supervisedHousingTotal,approvedHousing,approvedHousingTotal,spaceEquipmentPublicBuilding,spaceEquipmentPublicBuildingTotal,road,roadTotal,greenSpace,greenSpaceTotal,other,otherTotal},requesterProposal{active,fifteenPercentSupervisedOrApproved,acceptedByAuthority},alternativePlanningDetail,assignmentLocation,projectDescription,otherComment}, deliveredPermit{totalCharge,financialGuaranteeRequested,financialGuaranteeRequestedItem{amountRequested,depositDate,bank,guaranteeNumber,liftDate,entryDate,comment},chargeNature{receivableRequest{active,date,comment},startOfWorkRelatingToCharges{active,date,comment},introductionOfPermitApplicationForCharges{active,date,comment},implementationWithin2Years{active,date,comment},completeRealization{active,date,comment}},chargeCash{paymentOfChargeByTheApplicantOnTime,sumWithOtherCharges,linkedCase,paymentDate,bankAccount,comment,startWorkWith6YearsAfterPayment,moneyReallocation,timeLimitExtension,timeLimitExtensionMotif,timeLimitExtensionNotification,timeLimitSuspension,timeLimitSuspensionMotif,timeLimitSuspensionNotification,workFinancedByCharge,workFinancedByChargeDate,workFinancedByChargeComment,authorityRefund,authorityRefundDate,authorityRefundComment},freeCession{cessionDoneByRequester,date,comment}}}}}",
 "variables": {
 "arg": {
 "identifiersGroupInputs": [
 { "identifiersInputs": [ {
 "identifier": {
 "key": "'.$uuid.'",
 "type": "UUID"
 } } ] } ] } }}',
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if($statusCode === 200) {
                $content = $response->toArray();
            }
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['data']['Case_Details']['urbanCharge'] ?? [];
    }


    public function getLinkedCases(string $uuid, string $type = "UUID"): array
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $this->nova_connection->endpoint.'api/nova-api/graph/1.0.0/graphql', [
            'auth_bearer' => $this->nova_connection->token,
            'headers' => [
                'Content-Type' => 'application/json',
            ],

            'body' => '{"query": "query ($arg:IdentifiersInput!) { Case_LinkedCase_List (identifiers:$arg) {uuid, type, value}}",
    "variables": {
        "arg": {
            "identifiersGroupInputs": [
                {
                    "identifiersInputs": [
                        {
                            "identifier": {
                                "key": "'.$uuid.'",
                                "type": "UUID"
                            }
                        }
                    ]
                }
            ]
        }
    }}',
        ]);

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            if($statusCode === 200) {
                $content = $response->toArray();
            }
        } catch (TransportExceptionInterface $e) {
            var_dump($e->getMessage());
        }

        return $content['data']['Case_LinkedCase_List'] ?? [];
    }

    public function getReferencesJson(array $references, string $type = "ID"): string
    {
        $lines = '';
        $json = '{"identifiers":[{';

        foreach ($references as $reference) {
            $lines .= '"identifier":{"key":"'.$reference.'","type":"UUID","context":"CASE"},';
        };
        $json .= substr($lines, 0, -1).' }]}';

        return substr(stripslashes(json_encode($json, JSON_THROW_ON_ERROR)), 1,-1);
    }

    public function downloadDocument(string $identifier): string
    {
        return $this->getResponse($identifier)->getContent();
    }

    public function documentStatus(string $identifier): int
    {
        return $this->getResponse($identifier)->getStatusCode();
    }

    /**
     * @param string $identifier
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getResponse(string $identifier): ResponseInterface
    {
        $options = [
            'auth_bearer' => $this->nova_connection->token,
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ]
        ];

        if (isset($this->nova_connection->jwt_key)) {
            $options['headers']['x-jwt-api-key'] = $this->nova_connection->jwt_key;
        }

        return HttpClient::create()->request(
            'GET',
            $this->nova_connection->endpoint.'api/nova-api/document/1.0.0/download/identifier/UUID/'.$identifier,
            $options
        );
    }

    /**
     * @param array $references
     * @param string $type
     * @return array
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function queryDocumentsFromReferences(array $references, string $type): array
    {
        $content = [
            'auth_bearer' => $this->nova_connection->token,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],

            'body' => $this->getReferencesJson($references, $type),
        ];

        if (isset($this->nova_connection->jwt_key)) {
            $content['headers']['x-jwt-api-key'] = $this->nova_connection->jwt_key;
        }
        $httpClient = HttpClient::create();
        $response = $httpClient->request(
            'POST',
            $this->nova_connection->endpoint.'api/nova-api/graph/1.0.0/graphql',
            $content
        );

        return array($content, $response);
    }
}