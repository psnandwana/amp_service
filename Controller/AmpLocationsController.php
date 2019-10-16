<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * AmpLocations Controller
 *
 * @property \App\Model\Table\AmpLocationsTable $AmpLocations
 *
 * @method \App\Model\Entity\AmpLocation[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AmpLocationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $ampLocations = $this->paginate($this->AmpLocations);

        $this->set(compact('ampLocations'));
    }

    /**
     * View method
     *
     * @param string|null $id Amp Location id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ampLocation = $this->AmpLocations->get($id, [
            'contain' => []
        ]);

        $this->set('ampLocation', $ampLocation);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ampLocation = $this->AmpLocations->newEntity();
        if ($this->request->is('post')) {
            $ampLocation = $this->AmpLocations->patchEntity($ampLocation, $this->request->getData());
            if ($this->AmpLocations->save($ampLocation)) {
                $this->Flash->success(__('The amp location has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp location could not be saved. Please, try again.'));
        }
        $this->set(compact('ampLocation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Amp Location id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ampLocation = $this->AmpLocations->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ampLocation = $this->AmpLocations->patchEntity($ampLocation, $this->request->getData());
            if ($this->AmpLocations->save($ampLocation)) {
                $this->Flash->success(__('The amp location has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp location could not be saved. Please, try again.'));
        }
        $this->set(compact('ampLocation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Amp Location id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ampLocation = $this->AmpLocations->get($id);
        if ($this->AmpLocations->delete($ampLocation)) {
            $this->Flash->success(__('The amp location has been deleted.'));
        } else {
            $this->Flash->error(__('The amp location could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
