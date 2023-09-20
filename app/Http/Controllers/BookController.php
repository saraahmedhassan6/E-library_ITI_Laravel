<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Published_User;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;
use App\Policies\PermissionPolicy;



use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $Books = Book::orderBy('id', 'desc')->paginate(5);
        return view('Books.AllBooks',compact('Books'));
    }

    public function show($id)
    {
        $Books=Book::where('id',$id)->get();
        return view('Books.ShowBooks',compact('Books'));
    }

    public function Borrow(Request $request, $id)
    {
        $this->authorize('AllowBorrow', User::class);
        $Books = Book::findOrFail($id);
        $currentTime = Carbon::now();
        $returnedTime = $currentTime->copy()->addDays(7);
        $existingRecord = Published_User::where('user_id', $request->user_id)
        ->where('book_id', $id)
        ->first();
        if($Books->borrowed==0)
        {
            $Books->borrowed = 1;
            $Books->save();
            if ($existingRecord) {
                $existingRecord->update([
                    'check' => 1,
                    'returned' => $returnedTime,
                ]);
            }
            else {
                Published_User::create(
                    [
                        'user_id'=>$request->user_id,
                        'book_id'=>$id,
                        'returned' => $returnedTime,
                        'check' =>1 ,
                    ]);
            }
        }
        else
        {
            $Books->borrowed = 0;
            $Books->save();
            $existingRecord->update([
                'check' => 0,
            ]);
        }
        

        return redirect()->back();
    }
    public function Dashindex()
    {
        $this->authorize('Admin_Books', User::class);
        $Books = Book::orderBy('created_at', 'desc')->paginate(5);
        $Borrowed = Book::where('borrowed', 1)->orderBy('created_at', 'desc')->paginate(5);        
        $Categories=Category::all();
        return view('Dashboard.Books.book',compact('Books','Borrowed','Categories'));
    }
    
    public function store(Request $request)
    {
        $this->authorize('Admin_Books', User::class);
        $Book_img = '';
        if ($request->hasFile('book')) {
            $file = $request->file('book');
            $Book_img = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/Uploads/Books'), $Book_img);
        }
        $Auth_img = '';
        if ($request->hasFile('author_img')) {
            $file = $request->file('author_img');
            $Auth_img = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/Uploads/Authors'), $Auth_img);
        }
        
        Book::create(
            [
                'name'=>$request->name,
                'descr'=>$request->descr,
                'author'=>$request->author,
                'publication_date'=>$request->publication_date,
                'publisher'=>$request->publisher,
                'language'=>$request->language,
                'category_id'=>$request->category,
                'image'=>$Book_img,
                'author_image'=>$Auth_img,
                'borrowed'=>0,
            ]
            );
        return redirect('/Dashboard/Books');
    }

    public function update(Request $request)
    {
        $this->authorize('Admin_Books', User::class);
        $id=Book::findOrFail($request->id);
        $Book_img = $id->image;
        if ($request->hasFile('book')) {
            $file = $request->file('book');
            $Book_img = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/Uploads/Books'), $Book_img);
        }
        $Auth_img = $id->author_image;
        if ($request->hasFile('author_img')) {
            $file = $request->file('author_img');
            $Auth_img = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/Uploads/Authors'), $Auth_img);
        }
        
        $id->update(
            [
                'name'=>$request->name,
                'descr'=>$request->descr,
                'author'=>$request->author,
                'publication_date'=>$request->publication_date,
                'publisher'=>$request->publisher,
                'language'=>$request->language,
                'category_id'=>$request->category,
                'image'=>$Book_img,
                'author_image'=>$Auth_img,
            ]
            );
        return redirect('/Dashboard/Books');
    }

    public function destroy(Request $request)
    {
        $this->authorize('Admin_Books', User::class);
        $id=Book::findOrFail($request->id);
        if (Storage::disk('public')->exists('assets/Uploads/Books/' . $id->image)) {
            Storage::disk('public')->delete('assets/Uploads/Books/' . $id->image);
        }
        if (Storage::disk('public')->exists('assets/Uploads/Authors/' . $id->author_image)) {
            Storage::disk('public')->delete('assets/Uploads/Authors/' . $id->author_image);
        }
        $id->delete();
        return redirect('/Dashboard/Books');
    }

    public function UserBorrowed()
    {
        $this->authorize('User_Books', User::class);
        $borrowedBooks = Published_User::where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get();

        $allBorrowedBooks = Published_User::where('check', 1)
        ->orderBy('updated_at', 'desc')
        ->get();
        return view('Dashboard.Books.UserBorrowed',compact('borrowedBooks','allBorrowedBooks'));
    }

}
