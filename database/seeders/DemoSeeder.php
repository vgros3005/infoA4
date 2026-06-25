<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Priority;
use App\Models\RequestA4;
use App\Models\RequestType;
use App\Models\Role;
use App\Models\Software;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\TaskType;
use App\Models\Team;
use App\Models\TeamUserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanies();
        $this->seedSoftwares();
        $this->seedTeamsAndUsers();
        $this->seedRequestsA4();
    }

    private function seedCompanies(): void
    {
        $aCompanies = [
            ['name' => "FABRICATION D'APPLICATIONS ET DE REALISATIONS ELECTRONIQUES", 'code' => 'FARE', 'contact_email' => 'contact@fare.fr'],
            ['name' => 'SEFI', 'code' => 'SEFI', 'contact_email' => 'contact@sefi.fr'],
            ['name' => 'INTTEK', 'code' => 'INTTEK', 'contact_email' => 'antoine.talbot@inttek.fr'],
            ['name' => 'LA DETECTION ELECTRONIQUE FRANCAISE', 'code' => 'DEF', 'contact_email' => 'valentin.lecuyer@reseau-def.com'],
            ['name' => 'ALPHA SECURITE DISTRIBUTION', 'code' => 'ASD', 'contact_email' => 'jerrome.collet@asd-incendie.fr'],
        ];
        foreach ($aCompanies as $aCompany) {
            Company::updateOrCreate(['code' => $aCompany['code']], $aCompany);
        }
    }

    private function seedSoftwares(): void
    {
        $aSoftwares = [
            ['name' => 'IFS V9', 'code' => 'V9', 'vendor' => 'IFS', 'version' => '9'],
            ['name' => 'CRM Client', 'code' => 'CRM', 'vendor' => 'Salesforce', 'version' => 'xxx'],
            ['name' => 'IFS 10', 'code' => 'V10', 'vendor' => 'IFS', 'version' => '10 Upd 23'],
            ['name' => 'BOOMI', 'code' => 'FACT', 'vendor' => 'Boomi', 'version' => 'xx'],
            ['name' => 'Reporting BI', 'code' => 'BI', 'vendor' => 'Power BI', 'version' => 'Latest'],
        ];
        foreach ($aSoftwares as $aSoftware) {
            Software::updateOrCreate(['code' => $aSoftware['code']], $aSoftware);
        }
    }

    private function seedTeamsAndUsers(): void
    {
        $oAdmin = User::updateOrCreate(
            ['email' => 'admin@infoa4.local'],
            [
                'name'       => 'Administrateur',
                'first_name' => 'Admin',
                'last_name'  => 'Système',
                'password'   => Hash::make('password'),
                'is_active'  => true,
            ]
        );

        $oUsersData = [
            ['email' => 'chef.projet@infoa4.local', 'first_name' => 'Marie', 'last_name' => 'Martin', 'name' => 'Marie Martin'],
            ['email' => 'dev1@infoa4.local', 'first_name' => 'Pierre', 'last_name' => 'Dupont', 'name' => 'Pierre Dupont'],
            ['email' => 'dev2@infoa4.local', 'first_name' => 'Sophie', 'last_name' => 'Bernard', 'name' => 'Sophie Bernard'],
            ['email' => 'testeur@infoa4.local', 'first_name' => 'Lucas', 'last_name' => 'Petit', 'name' => 'Lucas Petit'],
            ['email' => 'demandeur@infoa4.local', 'first_name' => 'Julie', 'last_name' => 'Moreau', 'name' => 'Julie Moreau'],
        ];

        $aUsers = [$oAdmin];
        foreach ($oUsersData as $aUserData) {
            $aUsers[] = User::updateOrCreate(
                ['email' => $aUserData['email']],
                array_merge($aUserData, ['password' => Hash::make('password'), 'is_active' => true])
            );
        }

        $oTeamDev  = Team::updateOrCreate(['name' => 'Équipe Développement IFS'], ['color' => 'primary', 'description' => 'Équipe de développement IFS']);
        $oTeamTest = Team::updateOrCreate(['name' => 'Équipe QA / Tests'], ['color' => 'warning', 'description' => 'Équipe assurance qualité']);
        $oTeamMOA  = Team::updateOrCreate(['name' => 'MOA / Métier'], ['color' => 'success', 'description' => 'Maîtrise d\'ouvrage']);

        $oRoleAdmin = Role::where('name', 'admin')->first();
        $oRolePM    = Role::where('name', 'project_manager')->first();
        $oRoleDev   = Role::where('name', 'developer')->first();
        $oRoleTester = Role::where('name', 'tester')->first();
        $oRoleReq   = Role::where('name', 'requester')->first();

        $aMemberships = [
            [$oTeamDev, $aUsers[0], $oRoleAdmin],
            [$oTeamDev, $aUsers[1], $oRolePM],
            [$oTeamDev, $aUsers[2], $oRoleDev],
            [$oTeamDev, $aUsers[3], $oRoleDev],
            [$oTeamTest, $aUsers[1], $oRolePM],
            [$oTeamTest, $aUsers[4], $oRoleTester],
            [$oTeamMOA, $aUsers[5], $oRoleReq],
        ];

        foreach ($aMemberships as [$oTeam, $oUser, $oRole]) {
            TeamUserRole::updateOrCreate(
                ['team_id' => $oTeam->id, 'user_id' => $oUser->id],
                ['role_id' => $oRole->id, 'joined_at' => now()]
            );
        }
    }

    private function seedRequestsA4(): void
    {
        $oStatusDraft      = Status::where('name', 'draft')->first();
        $oStatusSubmitted  = Status::where('name', 'submitted')->first();
        $oStatusValidated  = Status::where('name', 'validated')->first();
        $oStatusInDev      = Status::where('name', 'in_development')->first();
        $oStatusInTest     = Status::where('name', 'in_testing')->first();
        $oStatusDeployed   = Status::where('name', 'deployed')->first();

        $oTypeBug          = RequestType::where('name', 'bug_fix')->first();
        $oTypeFeature      = RequestType::where('name', 'new_feature')->first();
        $oTypeImprovement  = RequestType::where('name', 'improvement')->first();

        $oPriorityHigh     = Priority::where('name', 'high')->first();
        $oPriorityMedium   = Priority::where('name', 'medium')->first();
        $oPriorityCritical = Priority::where('name', 'critical')->first();

        $oRequester = User::where('email', 'demandeur@infoa4.local')->first();
        $oAdmin     = User::where('email', 'admin@infoa4.local')->first();
        $oDev1      = User::where('email', 'dev1@infoa4.local')->first();
        $oDev2      = User::where('email', 'dev2@infoa4.local')->first();
        $oTeamDev   = Team::where('name', 'Équipe Développement')->first();

        $oCompanyAcme = Company::where('code', 'ACME')->first();
        $oSoftERP     = Software::where('code', 'ERP')->first();
        $oSoftCRM     = Software::where('code', 'CRM')->first();

        $oTaskTypeDev = TaskType::where('name', 'development')->first();
        $oTaskTypeTest = TaskType::where('name', 'testing')->first();

        $aRequestsData = [
            [
                'title'          => 'Correction bug calcul TVA ERP',
                'description'    => 'Le calcul de la TVA est incorrect pour les factures intra-UE depuis la mise à jour 2024.1',
                'request_type'   => $oTypeBug,
                'priority'       => $oPriorityCritical,
                'justification'  => 'Impact direct sur la facturation client et conformité fiscale',
                'status'         => $oStatusInDev,
                'requested_date' => now()->subDays(30),
                'desired_date'   => now()->addDays(5),
                'companies'      => [$oCompanyAcme],
                'softwares'      => [$oSoftERP],
                'estimated_hours' => 16,
            ],
            [
                'title'          => 'Nouveau tableau de bord KPI commercial',
                'description'    => 'Créer un tableau de bord consolidant les KPIs commerciaux depuis le CRM',
                'request_type'   => $oTypeFeature,
                'priority'       => $oPriorityHigh,
                'justification'  => 'Demande de la Direction Commerciale pour le reporting mensuel',
                'status'         => $oStatusValidated,
                'requested_date' => now()->subDays(20),
                'desired_date'   => now()->addDays(30),
                'companies'      => [$oCompanyAcme],
                'softwares'      => [$oSoftCRM],
                'estimated_hours' => 40,
            ],
            [
                'title'          => 'Optimisation performances portail RH',
                'description'    => 'Les temps de chargement des pages du portail RH dépassent 5 secondes',
                'request_type'   => $oTypeImprovement,
                'priority'       => $oPriorityMedium,
                'status'         => $oStatusInTest,
                'requested_date' => now()->subDays(45),
                'desired_date'   => now()->addDays(15),
                'companies'      => [],
                'softwares'      => [],
                'estimated_hours' => 24,
            ],
            [
                'title'          => 'Export PDF des bulletins de paie',
                'description'    => 'Permettre aux employés d\'exporter leur bulletin de paie en PDF depuis le portail RH',
                'request_type'   => $oTypeFeature,
                'priority'       => $oPriorityMedium,
                'status'         => $oStatusDeployed,
                'requested_date' => now()->subDays(90),
                'desired_date'   => now()->subDays(30),
                'companies'      => [],
                'softwares'      => [],
                'estimated_hours' => 20,
            ],
            [
                'title'          => 'Migration base de données vers MySQL 8',
                'description'    => 'Migration planifiée de la base de données de MySQL 5.7 vers MySQL 8.0',
                'request_type'   => RequestType::where('name', 'migration')->first(),
                'priority'       => $oPriorityHigh,
                'justification'  => 'Fin de support MySQL 5.7 prévue en octobre 2024',
                'status'         => $oStatusDraft,
                'requested_date' => now()->subDays(5),
                'desired_date'   => now()->addDays(60),
                'companies'      => [],
                'softwares'      => [],
                'estimated_hours' => 32,
            ],
        ];

        foreach ($aRequestsData as $aData) {
            $oRequest = RequestA4::create([
                'title'                  => $aData['title'],
                'description'            => $aData['description'],
                'content'                => '<p>' . $aData['description'] . '</p>',
                'request_type_id'        => $aData['request_type']->id,
                'priority_id'            => $aData['priority']->id,
                'priority_justification' => $aData['justification'] ?? null,
                'status_id'              => $aData['status']->id,
                'requester_id'           => $oRequester->id,
                'assigned_team_id'       => $oTeamDev->id,
                'requested_date'         => $aData['requested_date'],
                'desired_date'           => $aData['desired_date'],
                'estimated_hours'        => $aData['estimated_hours'],
                'is_frozen'              => in_array($aData['status']->name, ['validated', 'deployed']),
            ]);

            if (!empty($aData['companies'])) {
                $oRequest->companies()->sync(collect($aData['companies'])->pluck('id'));
            }
            if (!empty($aData['softwares'])) {
                $oRequest->softwares()->sync(collect($aData['softwares'])->pluck('id'));
            }

            StatusHistory::create([
                'request_a4_id'  => $oRequest->id,
                'from_status_id' => null,
                'to_status_id'   => Status::where('name', 'draft')->first()->id,
                'user_id'        => $oRequester->id,
                'action'         => 'create',
                'comment'        => 'Création de la demande',
            ]);

            if ($aData['status']->name !== 'draft') {
                StatusHistory::create([
                    'request_a4_id'  => $oRequest->id,
                    'from_status_id' => Status::where('name', 'draft')->first()->id,
                    'to_status_id'   => $aData['status']->id,
                    'user_id'        => $oAdmin->id,
                    'action'         => 'transition',
                    'comment'        => 'Transition automatique (données de démo)',
                ]);
            }

            if (in_array($aData['status']->name, ['in_development', 'in_testing', 'deployed'])) {
                $oTask1 = Task::create([
                    'request_a4_id'  => $oRequest->id,
                    'task_type_id'   => $oTaskTypeDev->id,
                    'assigned_to'    => $oDev1->id,
                    'created_by'     => $oAdmin->id,
                    'title'          => 'Développement : ' . substr($aData['title'], 0, 40),
                    'status'         => 'in_progress',
                    'priority'       => 'high',
                    'start_date'     => now()->subDays(10),
                    'end_date'       => now()->addDays(5),
                    'estimated_hours' => $aData['estimated_hours'] * 0.6,
                    'actual_hours'   => $aData['estimated_hours'] * 0.4,
                    'progress'       => 65,
                ]);

                TaskTimeEntry::create([
                    'task_id'    => $oTask1->id,
                    'user_id'    => $oDev1->id,
                    'entry_date' => now()->subDays(3),
                    'hours'      => 4,
                    'comment'    => 'Développement en cours',
                ]);

                $oTask2 = Task::create([
                    'request_a4_id'  => $oRequest->id,
                    'task_type_id'   => $oTaskTypeTest->id,
                    'assigned_to'    => $oDev2->id,
                    'created_by'     => $oAdmin->id,
                    'title'          => 'Tests : ' . substr($aData['title'], 0, 40),
                    'status'         => 'pending',
                    'priority'       => 'medium',
                    'start_date'     => now()->addDays(5),
                    'end_date'       => now()->addDays(10),
                    'estimated_hours' => $aData['estimated_hours'] * 0.3,
                    'progress'       => 0,
                ]);
            }
        }
    }
}
