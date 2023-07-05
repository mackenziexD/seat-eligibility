<?php

namespace Busa\Seat\Http\Controllers\Character;

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Seat\Web\Http\DataTables\Scopes\CharacterScope;

class EligibilityController extends Controller
{
    /**
     * Gets the zkill stats for a character
     * https://zkillboard.com/api/stats/characterID/{id}}/
     * variables: id
     * 
    */
    public function pullZkillStats($id){
        $client = new \GuzzleHttp\Client();
    
        $res = $client->request('GET', 'https://zkillboard.com/api/stats/characterID/'.$id.'/', [
            'headers' => [
                'Accept-Encoding' => 'gzip',
                'User-Agent' => 'Helious Jin-Mei/BUSA SeAT'
            ]
        ]);
    
        $body = $res->getBody();
        $json = json_decode($body, true);
        
        return $json;
    }
    /**
     * Show the eligibility checker.
     *
     * @return \Illuminate\Http\Response
     */
     public function index(CharacterInfo $character, Request $request)
    {
        $allCharacters = $character->refresh_token->user->all_characters()->sortBy('id');
        $titans = [
            '671',
            '11567',
            '3764',
            '23773',
            '42126',
            '42241',
            '45649'
        ];
        $supers = [
            '3514',
            '22852',
            '23913',
            '23919',
            '23917',
            '42125'
        ];
        $carriers = [
            '23757',
            '23911',
            '23915',
            '24483'
        ];
        $dreads = [
            '19720',
            '19722',
            '19724',
            '19726',
            '42124',
            '42243',
            '45647',
            '52907',
            '73787',
            '73790',
            '73792',
            '73793',
            '77281',
            '77283',
            '77284',
            '77288'
        ];
        $faxess = [
            '37604',
            '37605',
            '37606',
            '37607',
            '42133',
            '42242',
            '45645'
        ];
        // create an array of id's
        $ids = [
        ];

        $allAssetsWereLookingFor = [];
        $meets3MonthKillRequirement = false;
        // loop through all characters
        foreach($allCharacters as $char) {
            $assetsWereLookingFor = [
                'character_id' => $char->character_id,
                'name' => $char->name,
                'hasTitan' => false,
                'hasSuper' => false,
                'hasCarrier' => false,
                'hasDread' => false,
                'hasFAX' => false,
                'totalKillsOver3Months' => '0',
            ];
        
            $char->assets = CharacterAsset::where('character_id', $char->character_id)->get();
        
            foreach($char->assets as $asset) {
                if(in_array($asset->type_id, $titans)) {
                    $assetsWereLookingFor['hasTitan'] = true;
                }
                if(in_array($asset->type_id, $supers)) {
                    $assetsWereLookingFor['hasSuper'] = true;
                }
                if(in_array($asset->type_id, $carriers)) {
                    $assetsWereLookingFor['hasCarrier'] = true;
                }
                if(in_array($asset->type_id, $dreads)) {
                    $assetsWereLookingFor['hasDread'] = true;
                }
                if(in_array($asset->type_id, $faxess)) {
                    $assetsWereLookingFor['hasFAX'] = true;
                }
            }

            $zkillStats = $this->pullZkillStats($char->character_id);

            if(isset($zkillStats['months']) || !empty($zkillStats['months'])) { 
                $zkillStats = $zkillStats['months'];
                $currentYearMonth = date('Ym');
                $lastYearMonth = date('Ym', strtotime('-1 month'));
                $secondLastYearMonth = date('Ym', strtotime('-2 month'));
                $thirdLastYearMonth = date('Ym', strtotime('-3 month'));

                $totalKillsOver3Months = 0;
                foreach($zkillStats as $Ym => $stats){
                    if($Ym == $currentYearMonth || $Ym == $lastYearMonth || $Ym == $secondLastYearMonth || $Ym == $thirdLastYearMonth){
                        $totalKillsOver3Months += $stats['shipsDestroyed'];
                    }
                }
                $assetsWereLookingFor['totalKillsOver3Months'] = $totalKillsOver3Months;
            }; 


        
            // add the $assetsWereLookingFor for this character to the overall array
            $allAssetsWereLookingFor[] = $assetsWereLookingFor;

            sleep(0.5);
        }

        $ThreeMonthKills = 0;
        foreach($allAssetsWereLookingFor as $char){
            if($char === 'false' || $char === false) continue;
            if($char['totalKillsOver3Months'] > 0) {
                $ThreeMonthKills += $char['totalKillsOver3Months'];
            }
        }

        if($ThreeMonthKills >= 0) {
            $meets3MonthKillRequirement = true;
        }
        $hasATitan = false;
        $hasASuper = false;
        $hasACarrier = false;
        $hasADread = false;
        $hasAFAX = false;
        // if any of the characters have a titan, super, carrier, dread, or fax, then the main character is eligible
        foreach($allAssetsWereLookingFor as $char){
            if($char === false || $char === false) continue;
            if($char['hasTitan']) {
                $hasATitan = true;
            }
            if($char['hasSuper']) {
                $hasASuper = true;
            }
            if($char['hasCarrier']) {
                $hasACarrier = true;
            }
            if($char['hasDread']) {
                $hasADread = true;
            }
            if($char['hasFAX']) {
                $hasAFAX = true;
            }
        }

        $mainCharacter = $character->refresh_token->user->main_character->name;

        return view('seat-busa::eligibility.index', compact('character', 'allAssetsWereLookingFor', 'meets3MonthKillRequirement', 'mainCharacter', 'hasATitan', 'hasASuper', 'hasACarrier', 'hasADread', 'hasAFAX'));
    }
}
