<?php

namespace Helious\SeatEligibility\Http\Controllers\Character;

use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Assets\CharacterAsset;

use Seat\Eveapi\Models\Killmails\KillmailAttacker;
use Seat\Eveapi\Models\Killmails\KillmailVictim;
use Seat\Eveapi\Models\Killmails\KillmailDetail;
use Seat\Eveapi\Models\Killmails\Killmail;

use Seat\Eveapi\Models\Character\CharacterSkill;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Seat\Web\Http\DataTables\Scopes\CharacterScope;

class EligibilityController extends Controller
{    
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



            $attacks = KillmailAttacker::with('character')
                ->where('character_id', $char->character_id)
                ->where('created_at', '>=', now()->subMonths(3))
                ->get();

            $attacksCount = $attacks->count();
            
            $assetsWereLookingFor['totalKillsOver3Months'] = $attacksCount;

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
        if($ThreeMonthKills >= 40) $meets3MonthKillRequirement = true;

        $hasHull = [
            'Titan' => false,
            'Super' => false,
            'Carrier' => false,
            'Dread' => false,
            'FAX' => false,
        ];
        $hasSkills = [
            'Titan' => false,
            'Super' => false,
            'Carrier' => false,
            'Dread' => false,
            'FAX' => false,
        ];

        foreach($allAssetsWereLookingFor as $char){
            if($char === false) continue;
            if($char['hasTitan']) {
                $hasHull['Titan'] = true;
            }
            if($char['canFlyTitan']) {
                $hasSkills['Titan'] = true;
            }
            if($char['hasSuper']) {
                $hasHull['Super'] = true;
            }
            if($char['canFlySuper']) {
                $hasSkills['Super'] = true;
            }
            if($char['hasCarrier']) {
                $hasHull['Carrier'] = true;
            }
            if($char['canFlyCarrier']) {
                $hasSkills['Carrier'] = true;
            }
            if($char['hasDread']) {
                $hasHull['Dread'] = true;
            }
            if($char['canFlyDread']) {
                $hasSkills['Dread'] = true;
            }
            if($char['hasFAX']) {
                $hasHull['FAX'] = true;
            }
            if($char['canFlyFAX']) {
                $hasSkills['FAX'] = true;
            }
        }

        $mainCharacter = $character->refresh_token->user->main_character->name;

        return view('seat-eligibility::eligibility.index', compact('character', 'allAssetsWereLookingFor', 'meets3MonthKillRequirement', 'mainCharacter', 'hasHull', 'hasSkills'));
    }
}
