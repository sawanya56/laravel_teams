<?php

$tenantId = 'YOUR_TENANT_ID';
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Get an access token
$accessToken = getAccessToken($tenantId, $clientId, $clientSecret);


// Create a Microsoft 365 group
$groupDisplayName = 'New Group';
$groupDescription = 'This is a new group';

$groupData = array(
    'displayName' => $groupDisplayName,
    'description' => $groupDescription,
    'groupTypes' => ['Unified'],
    'mailEnabled' => true,
    'mailNickname' => strtolower(str_replace(' ', '', $groupDisplayName)),
    'securityEnabled' => false
);

$createGroupEndpoint = 'https://graph.microsoft.com/v1.0/groups';

$ch = curl_init($createGroupEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($groupData));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// Handle the response
if ($response) {
    $group = json_decode($response, true);
    $groupId = $group['id'];
    echo "Group created with ID: $groupId";
} else {
    echo "Failed to create the group.";
}

function getAccessToken($tenantId, $clientId, $clientSecret)
{
    $tokenEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/token";

    $postData = array(
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials',
        'resource' => 'https://graph.microsoft.com'
    );

    $ch = curl_init($tokenEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);
    return $tokenData['access_token'];
}

?>