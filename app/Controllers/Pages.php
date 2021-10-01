<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Home | WebProgramming',
            'tes' => ['satu','dua','tiga']
        ];

        
        return view('pages/home', $data);
       
    }

    public function about()
    {
        $data = [
            'title' => 'About | WebProgramming'
        ];
      
        return view('pages/about', $data);
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact Us',
            'alamat' => [
                [
                    
                ]
            ]
        ];

        return view('pages/contact', $data);
    }
}




