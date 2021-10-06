<?php

namespace App\Controllers;
use App\Models\KomikModel;

class Komik extends BaseController
{
    protected $komikModel;

    public function __construct()
    {
        $this->komikModel = new KomikModel();
    }

    public function index()
    {
        $data = [
            'title' => " Daftar Komik",
            'komik' => $this->komikModel->getKomik()
        ];

        
        // dd($komik); untuk cek data dengan table
        //cara konek db tanpa model
        // $db = \Config\Database::connect();
        // $komik = $db->query("SELECT * FROM komik");
        // foreach($komik->getResultArray() as $row){
        //     d($row);
        // }
        
        return view('komik/index', $data);
    }

    public function detail($slug){
    //    $komik = $this->komikModel->getKomik($slug); jika tanpa Model
       $data = [
           'title' =>  'Detail Komik',
           'komik' => $this->komikModel->getKomik($slug)
       ];

       //jika komik tidak ada di tabel
       if(empty($data['komik'])){
           throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul Komik' . $slug . 'Tidak Ditemukan');
       }
       return view('komik/detail', $data);
    }

    public function create()
    {
        // session(); sesion di pindahkan ke base controller karena dipakai secara global
        $data = [
            'title' => 'Form Tambah Komik',
            'validation' => \Config\Services::validation()
        ];
        return view('komik/create', $data);
    }

    public function save()
    {
        //validasi input
        if(!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} Komik harus diisi',
                    'is_unique' => '{field} Komik Sudah Terdaftar'
                ]
                ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    // 'uploaded' => 'Pilih gambar sampul terlebih dahulu',
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ]
            
        ])) {
            // $validation = \Config\Services::validation();
            // dd($validation); untuk menguji
            // return redirect()->to('/Komik/create')->withInput()->with('validation', $validation);
            return redirect()->to('/Komik/create')->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');
        /// apakah tidak ada gambar yang diupload
        if($fileSampul->getError() == 4){
            $namaSampul = 'default.jpg';
        } else{
                // Generate nama random
                $namaSampul = $fileSampul->getRandomName();
                /// pindahkan file ke folder img
                $fileSampul->move('img', $namaSampul);
                // ambil nama file sampul
        }
       
        // $namaSampul = $fileSampul->getName();

        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data Berhasil Ditambahkan.');

        return redirect()->to('/komik');
    }

    public function delete($id)
    {
        //cari gambar berdasarkan id
        $komik = $this->komikModel->find($id);

        //jika file gambar default 
        if($komik['sampul'] != 'default.jpg')
        {
            unlink('img/' . $komik['sampul']);
        }

        $this->komikModel->delete($id);
        session()->setFlashdata('pesan', 'Data Berhasil Ditambahkan.');
        return redirect()->to('/komik');
    }

    public function edit($slug)
    {
        $data = [
            'title' => 'Form Ubah Komik',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slug)
        ];

        return view('komik/edit', $data);
    }

    public function update($id)
    {
        // dd($this->request->getVar());
         //validasi update
         //cek judul
         $komikLama = $this->komikModel->getKomik($this->request->getVar('slug'));
         if($komikLama['judul'] == $this->request->getVar('judul')){
             $rule_judul = 'required';
         } else{
             $rule_judul = 'required|is_unique[komik.judul]';
         }

         if(!$this->validate([
            'judul' => [
                'rules' => $rule_judul,
                'errors' => [
                    'required' => '{field} Komik harus diisi',
                    'is_unique' => '{field} Komik Sudah Terdaftar'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    // 'uploaded' => 'Pilih gambar sampul terlebih dahulu',
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar'
                ]
            ]
            
        ])) {
            return redirect()->to('/Komik/edit/' . $this->request->getVar('slug'))->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');
        //cek gambar, apakah tetap gambar lama
        if($fileSampul->getError() == 4){
            $namaSampul = $this->request->getVar('sampulLama');
        }else {
            ///generate nama file random
            $namaSampul = $fileSampul->getRandomName();
            /// pindahkan gambar 
            $fileSampul->move('img', $namaSampul);
            //hapus file yang lama
            unlink('img/'. $this->request->getVar('sampulLama'));
        }

        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data Berhasil Diubah.');

        return redirect()->to('/komik');
    }

}

