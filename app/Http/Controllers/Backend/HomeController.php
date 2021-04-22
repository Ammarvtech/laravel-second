<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Genres\GenresContract;
use Contracts\Users\UsersContract;
use Contracts\Shows\ShowsContract;
use Contracts\Casts\CastsContract;
use Contracts\Movies\MoviesContract;
use Contracts\Episodes\EpisodesContract;
use Contracts\Videos\VideosContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Country;
use App\User;
use PDF;
class HomeController extends Controller
{

	public function __construct(GenresContract $genres, UsersContract $users, ShowsContract $shows,
CastsContract $casts, MoviesContract $movies, EpisodesContract $episodes, VideosContract $videos)
    {
        $this->genres = $genres;
        $this->users = $users;
        $this->shows  = $shows;
		$this->casts  = $casts;
		$this->movies = $movies;
		$this->episodes = $episodes;
        $this->videos   = $videos;
    }

    public function index(Request $request)
    {
        //$dataArray = array('movies','genres','shows','episodes','casts','users');        
        $dataArray = array('shows','movies','genres','episodes','casts','users');
        $today  = Carbon::today()->toDateString();
        $weekly = Carbon::today()->subDays(7)->toDateString();
        $monthly = Carbon::today()->subDays(30)->toDateString();
        
        foreach ($dataArray as $key => $value) {
            $data['today_'.$value] = $this->$value->dateFilter($today , $today)->count;
            $data['weekly_'.$value] = $this->$value->dateFilter($today , $weekly)->count;
            $data['monthly_'.$value] = $this->$value->dateFilter($today , $monthly)->count;
            $data[$value] = $this->$value->countAll();
        }

        $result = $this->revenue();
        $data['search'] = 0;
    	$data['usersData'] = $result['usersData'];
        //$data['revenue'] = $result['revenue'];
        //$data['trendingMovies'] = $this->movies->getTrending(10);
        //$data['trendingShows'] = $this->shows->getTrending(10);
        $data['total_visitors'] = $this->users->totalVisitors($today, $today);
        $data['site_visitors'] = DB::table('orders')->select('country', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->where('description', '!=', 'Free Trial')->groupBy('country')->get();
        //$data['videos'] = $this->videos->uploadedToday($today, $today);
        //$data['allVideos'] = $this->videos->getAll();
        //$data['allUsers'] = $this->users->getAllByRole();
        $data['page_title'] = "Dashboard";
        return view('backend.home',$data);
    }

    public function filter(Request $request)
    {
        $dataArray = array('shows','movies','genres','episodes','casts','users');
        foreach ($dataArray as $key => $value) {
            $data[$value] = $this->$value->dateFilter($request['to_date'], $request['from_date'])->count;
        }
        $result = $this->revenue();
        $data['search'] = 1;
        $data['usersData'] = $result['usersData'];
        $data['total_visitors'] = $this->users->totalVisitors($request['to_date'], $request['from_date']);
        //$data['revenue'] = $result['revenue'];
        //$data['trendingMovies'] = $this->movies->getTrending(10);
        //$data['trendingShows'] = $this->shows->getTrending(10);
        $data['site_visitors'] = DB::table('orders')->select('country', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->where('description', '!=', 'Free Trial')->groupBy('country')->get();
        //$data['videos'] = $this->videos->uploadedToday($request['to_date'], $request['from_date']);
        //$data['allVideos'] = $this->videos->getAll();
        //$data['allUsers'] = $this->users->getAllByRole();
        $data['page_title'] = "Dashboard";
        return view('backend.home',$data);
    }

    protected function revenue(){
        /*$total_orders = DB::table('orders')
         ->select('country', DB::raw('sum(amount) as amount'))
         ->groupBy('country')
         ->get();*/

        $user = DB::table('users')
         ->select('country', DB::raw('count(*) as total'))
         ->where('country', '!=', 'European Union')
         ->where('role_id', '=', 1)
         ->groupBy('country')
         ->get();

         $european = DB::table('users')
         ->select('city', DB::raw('count(*) as total'))
         ->where('country', '=', 'European Union')
         ->where('role_id', '=', 1)
         ->groupBy('city')
         ->get();

        $users = "['Country', 'Users'],";

        foreach ($user as $key => $value) {
            $users .= "['".$value->country."',".$value->total."],";
        }

        foreach ($european as $key => $eu) {
           $users .= "['".$eu->city."',".$eu->total."],";
        }

        $users = rtrim($users, ",");
        $data['usersData'] = $users;
        //$data['revenue'] = $total_orders;
        return $data;
    }

    public function printPDF(){
        $dataArray = array('shows','movies','genres','episodes','casts','users');
        $today  = Carbon::today()->toDateString();
        $weekly = Carbon::today()->subDays(7)->toDateString();
        $monthly = Carbon::today()->subDays(30)->toDateString();
        
        foreach ($dataArray as $key => $value) {
            $data['today_'.$value] = $this->$value->dateFilter($today , $today)->count;
            $data['weekly_'.$value] = $this->$value->dateFilter($today , $weekly)->count;
            $data['monthly_'.$value] = $this->$value->dateFilter($today , $monthly)->count;
            $data[$value] = $this->$value->countAll();
        }

        $total_orders = DB::table('orders')
         ->select('country', DB::raw('sum(amount) as amount'))
         ->groupBy('country')
         ->get();
         $data['date'] = $today;
        $data['revenue'] = $total_orders;
        $data['trendingMovies'] = $this->movies->getTrending(10);
        $data['trendingShows'] = $this->shows->getTrending(10);
        $data['videos'] = $this->videos->uploadedToday($today, $today)->count();
        $data['allVideos'] = $this->videos->getAll()->count();
        $data['total_visitors'] = $this->users->totalVisitors($today, $today);
        $data['site_visitors'] = DB::table('orders')->select('country', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->groupBy('country')->where('description', '!=', 'Free Trial')->get();
        
       /* $html = view('backend/report',$data)
        $mpdf =  mPDF::WriteHTML($html);
        $mpdf= mPDF::Output('report.pdf', 'D');*/

        $pdf = PDF::loadView('backend/report', $data);
        return $pdf->download('report.pdf');
    }

    public function statistics(Request $request){
        $data['page_title'] = "Statistics";
        return view('backend.statistics',$data);
    }

    public function topWatching(Request $request){
        $data['trendingMovies'] = $this->movies->getTrending(10);
        $data['trendingShows'] = $this->shows->getTrending(10);
        $data['page_title'] = "Top Watching Titles";
        return view('backend.top_watching_titles',$data);
    }

    public function allFiles(Request $request){
        $data['allVideos'] = $this->videos->getAll();
        $data['page_title'] = "Videos";
        return view('backend.all_files',$data);
    }

    public function allUsers(Request $request){
        $data['allUsers'] = $this->users->getAllByRole();
        $data['page_title'] = "Users";
        return view('backend.all_users',$data);
    }

    public function uploadedToday(){
        $today  = Carbon::today()->toDateString();
        $data['videos'] = $this->videos->uploadedToday($today, $today);
        $data['page_title'] = "Uploaded Today";
        return view('backend.uploaded_today',$data);
    }
}
