<?php

namespace App\Http\Controllers;

use HMS\Entities\Role;
use HMS\Governance\VotingManager;
use HMS\Repositories\Members\BoxRepository;
use HMS\Repositories\Members\ProjectRepository;
use HMS\Repositories\Membership\RejectedLogRepository;
use HMS\Repositories\RoleRepository;
use HMS\Repositories\Snackspace\TransactionRepository;
use HMS\Repositories\Tools\BookingRepository;
use HMS\Repositories\Tools\ToolRepository;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var BoxRepository
     */
    protected $boxRepository;

    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var BookingRepository
     */
    protected $bookingRepository;

    /**
     * @var ToolRepository
     */
    protected $toolRepository;

    /**
     * @var VotingManager
     */
    protected $votingManager;

    /**
     * @var RejectedLogRepository
     */
    protected $rejectedLogRepository;

    /**
     * Create a new controller instance.
     *
     * @param ProjectRepository $projectRepository
     * @param BoxRepository $boxRepository
     * @param TransactionRepository $transactionRepository
     * @param RoleRepository $roleRepository
     * @param BookingRepository $bookingRepository
     * @param ToolRepository $toolRepository
     * @param VotingManager $votingManager
     * @param RejectedLogRepository $rejectedLogRepository
     *
     * @return void
     */
    public function __construct(
        ProjectRepository $projectRepository,
        BoxRepository $boxRepository,
        TransactionRepository $transactionRepository,
        RoleRepository $roleRepository,
        BookingRepository $bookingRepository,
        ToolRepository $toolRepository,
        VotingManager $votingManager,
        RejectedLogRepository $rejectedLogRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->boxRepository = $boxRepository;
        $this->transactionRepository = $transactionRepository;
        $this->roleRepository = $roleRepository;
        $this->bookingRepository = $bookingRepository;
        $this->toolRepository = $toolRepository;
        $this->votingManager = $votingManager;
        $this->rejectedLogRepository = $rejectedLogRepository;
    }

    /**
     * Show the application welcome screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function welcome()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('welcome');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRoleByName(Role::MEMBER_APPROVAL)) {
            $rejectedLogs = $this->rejectedLogRepository->findByUser($user);
            if (! empty($rejectedLogs) &&
                is_null(array_pop($rejectedLogs)->getUserUpdatedAt())) {
                return redirect()->route('membership.edit', $user->getId());
            }

            return view('pages.awaitingApproval');
        } elseif ($user->hasRoleByName(Role::MEMBER_PAYMENT)) {
            return view('pages.awaitingPayment');
        }

        $projects = $this->projectRepository->paginateByUser($user, 5, 'page', true);
        $boxCount = $this->boxRepository->countInUseByUser($user);
        $snackspaceTransactions = $this->transactionRepository->paginateByUser($user, 3);
        $teams = $this->roleRepository->findTeamsForUser($user);
        $bookings = $this->bookingRepository->findFutureByUser($user);
        $tools = $this->toolRepository->findAll();
        $toolIds = array_map(function ($tool) {
            return $tool->getId();
        }, $tools);
        $votingStatus = $this->votingManager->getVotingStatusForUser($user);

        return view('home')->with([
            'user' => $user,
            'projects' => $projects,
            'boxCount' => $boxCount,
            'snackspaceTransactions' => $snackspaceTransactions,
            'teams' => $teams,
            'bookings' => $bookings,
            'toolIds' => $toolIds,
            'votingStatus' => $votingStatus,
        ]);
    }
}
