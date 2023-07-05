<?php

namespace Busa\Seat\Http\Controllers\Character;

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Character\CharacterSkill;
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
        
        /////////////
        /// TYPE ID'S
        /////////////
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
        $faxes = [
            '37604',
            '37605',
            '37606',
            '37607',
            '42133',
            '42242',
            '45645'
        ];
        /////////////

        /////////////
        //// SKILL ID'S
        /////////////
        $titansSkills = [
            '3344',
            '3345',
            '3346',
            '3347',
        ];
        $supersSkills = [
            '32339',
        ];
        $carriersSkills = [
            '24311',
            '24312',
            '24313',
            '24314',
        ];
        $dreadsSkills = [
            '52997',
            '77738',
            '20525',
            '20530',
            '20531',
            '20532',
        ];
        $faxSkills = [
            '27906',
            '40535',
            '40536',
            '40537',
            '40538',
        ];
        /////////////

        $allAssetsWereLookingFor = [];
        $meets3MonthKillRequirement = false;

        foreach($allCharacters as $char) {
            $assetsWereLookingFor = [
                'character_id' => $char->character_id,
                'name' => $char->name,
                'hasTitan' => false,
                'canFlyTitan' => false,
                'hasSuper' => false,
                'canFlySuper' => false,
                'hasCarrier' => false,
                'canFlyCarrier' => false,
                'hasDread' => false,
                'canFlyDread' => false,
                'hasFAX' => false,
                'canFlyFAX' => false,
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
                if(in_array($asset->type_id, $faxes)) {
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
            } else {
                $assetsWereLookingFor['totalKillsOver3Months'] = '0';
            }

            
            $char->skills = CharacterSkill::where('character_id', $char->character_id)->get();

            foreach($char->skills as $skill) {
                if(in_array($skill->skill_id, $titansSkills) && $skill->trained_skill_level >= 1) {
                    $assetsWereLookingFor['canFlyTitan'] = true;
                }
                if(in_array($skill->skill_id, $supersSkills) && $skill->trained_skill_level >= 1) {
                    $assetsWereLookingFor['canFlySuper'] = true;
                }
                if(in_array($skill->skill_id, $carriersSkills) && $skill->trained_skill_level >= 1) {
                    $assetsWereLookingFor['canFlyCarrier'] = true;
                }
                if(in_array($skill->skill_id, $dreadsSkills) && $skill->trained_skill_level >= 1) {
                    $assetsWereLookingFor['canFlyDread'] = true;
                }
                if(in_array($skill->skill_id, $faxSkills) && $skill->trained_skill_level >= 1) {
                    $assetsWereLookingFor['canFlyFAX'] = true;
                }
            }
        
            $allAssetsWereLookingFor[] = $assetsWereLookingFor;
            sleep(2);
            // MUST SLEEP OTHERWISE ZKILL WILL RATE LIMIT, :BLEACH:
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
