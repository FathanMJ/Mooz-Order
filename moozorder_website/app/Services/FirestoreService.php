<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    protected $db;

    public function __construct()
    {
        $this->db = new FirestoreClient([
            'keyFilePath' => app_path('Services/serviceAccountKey.json'),
            'projectId' => 'mooz-order', // ganti dengan projectId Firebase kamu
        ]);
    }

    public function saveNotification($data, $id = null)
    {
        $collection = $this->db->collection('notifications');
        if ($id) {
            $collection->document($id)->set($data);
        } else {
            $collection->add($data);
        }
    }

    public function deleteNotification($id)
    {
        $collection = $this->db->collection('notifications');
        $collection->document($id)->delete();
    }
}