<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * AmpFlats Controller
 *
 * @property \App\Model\Table\AmpFlatsTable $AmpFlats
 *
 * @method \App\Model\Entity\AmpFlat[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AmpFlatsController extends AppController
{
   
    public function index()
    {
        $ampFlats = $this->paginate($this->AmpFlats);

        $this->set(compact('ampFlats'));
    }

    public function create()
    {
        $ampFlat = $this->AmpFlats->newEntity();
        if ($this->request->is('post')) {
            $ampFlat = $this->AmpFlats->patchEntity($ampFlat, $this->request->getData());
            if ($this->AmpFlats->save($ampFlat)) {
                $this->Flash->success(__('The amp flat has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp flat could not be saved. Please, try again.'));
        }
        $this->set(compact('ampFlat'));
    }
}
