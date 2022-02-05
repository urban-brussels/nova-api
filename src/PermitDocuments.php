<?php

namespace UrbanBrussels\NovaApi;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PermitDocuments
{
    private NovaConnection $nova_connection;

    public function __construct(NovaConnection $nova_connection)
    {
        $this->nova_connection = $nova_connection;
    }

    public function listDocumentsFromReferences(array $references, string $type = "ID"): array
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
        $response = $httpClient->request('POST', $this->nova_connection->endpoint . 'api/nova-api/document/1.0.0/list/', $content);

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

        return $content['data'] ?? [];
    }

    /**
     * @throws \JsonException
     */
    public function getReferencesJson(array $references, string $type = "ID")
    {
        $identifiers["identifiers"] = array();
        $nb = is_countable($references) ? count($references) : 0;

        for ($i = 0; $i < $nb; $i++)
        {
            $identifiers["identifiers"][$i]['identifier']['key'] = $references[$i];
            $identifiers["identifiers"][$i]['identifier']['type'] = $type;
            // CONTEXT
            $identifiers["identifiers"][$i]['identifier']['context'] = 'CASE';
        }

        return json_encode($identifiers, JSON_THROW_ON_ERROR);
    }


    public function downloadDocument(string $identifier)
    {
        $content = [
            'auth_bearer' => $this->nova_connection->token,
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ]
        ];

        if (isset($this->nova_connection->jwt_key)) {
            $content['headers']['x-jwt-api-key'] = $this->nova_connection->jwt_key;
        }

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->nova_connection->endpoint . 'api/nova-api/document/1.0.0/download/identifier/UUID/' . $identifier, $content);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        return $content ?? null;
    }
}