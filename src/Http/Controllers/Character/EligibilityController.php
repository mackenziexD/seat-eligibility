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
        
            // Get the assets
            $assets = CharacterAsset::where('character_id', $char->character_id)->pluck('type_id');

            $assetsWereLookingFor = array_merge($assetsWereLookingFor, [
                'hasTitan' => $assets->contains(function ($value) use ($titans) {
                    return in_array($value, $titans);
                }),
                'hasSuper' => $assets->contains(function ($value) use ($supers) {
                    return in_array($value, $supers);
                }),
                'hasCarrier' => $assets->contains(function ($value) use ($carriers) {
                    return in_array($value, $carriers);
                }),
                'hasDread' => $assets->contains(function ($value) use ($dreads) {
                    return in_array($value, $dreads);
                }),
                'hasFAX' => $assets->contains(function ($value) use ($faxes) {
                    return in_array($value, $faxes);
                }),
            ]);

            $allSkillIds = array_merge($titansSkills, $supersSkills, $carriersSkills, $dreadsSkills, $faxSkills);

            // Get the skills
            $skills = CharacterSkill::where('character_id', $char->character_id)
                ->whereIn('skill_id', $allSkillIds)
                ->get();

            // Update the array with skill checks
            $assetsWereLookingFor = array_merge($assetsWereLookingFor, [
                'canFlyTitan' => $skills->contains(function ($skill) use ($titansSkills) {
                    return in_array($skill->skill_id, $titansSkills) && $skill->trained_skill_level >= 1;
                }),
                'canFlySuper' => $skills->contains(function ($skill) use ($supersSkills) {
                    return in_array($skill->skill_id, $supersSkills) && $skill->trained_skill_level >= 1;
                }),
                'canFlyCarrier' => $skills->contains(function ($skill) use ($carriersSkills) {
                    return in_array($skill->skill_id, $carriersSkills) && $skill->trained_skill_level >= 1;
                }),
                'canFlyDread' => $skills->contains(function ($skill) use ($dreadsSkills) {
                    return in_array($skill->skill_id, $dreadsSkills) && $skill->trained_skill_level >= 1;
                }),
                'canFlyFAX' => $skills->contains(function ($skill) use ($faxSkills) {
                    return in_array($skill->skill_id, $faxSkills) && $skill->trained_skill_level >= 1;
                }),
            ]);

            $attacks = KillmailAttacker::with('character')
                ->where('character_id', $char->character_id)
                ->where('created_at', '>=', now()->subMonths(3))
                ->get();
            $attacksCount = $attacks->count();
            $assetsWereLookingFor['totalKillsOver3Months'] = $attacksCount;
        
            $allAssetsWereLookingFor[] = $assetsWereLookingFor;
        }

        $ThreeMonthKills = 0;

        foreach($allAssetsWereLookingFor as $char){
            if($char === 'false' || $char === false) continue;
            if($char['totalKillsOver3Months'] > 0) {
                $ThreeMonthKills += $char['totalKillsOver3Months'];
            }
        }

        if($ThreeMonthKills >= 40) $meets3MonthKillRequirement = true;

        $shipTypes = ['Titan', 'Super', 'Carrier', 'Dread', 'FAX'];
        $hasHull = array_fill_keys($shipTypes, false);
        $hasSkills = array_fill_keys($shipTypes, false);

        foreach ($allAssetsWereLookingFor as $char) {
            if ($char === false) continue;

            foreach ($shipTypes as $shipType) {
                if (isset($char['has' . $shipType]) && $char['has' . $shipType]) {
                    $hasHull[$shipType] = true;
                }

                if (isset($char['canFly' . $shipType]) && $char['canFly' . $shipType]) {
                    $hasSkills[$shipType] = true;
                }
            }
        }

        $mainCharacter = $character->refresh_token->user->main_character->name;

        return view('seat-eligibility::eligibility.index', compact('character', 'allAssetsWereLookingFor', 'meets3MonthKillRequirement', 'mainCharacter', 'hasHull', 'hasSkills'));
    }
}
