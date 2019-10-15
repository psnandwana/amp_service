<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * AmpEmployeesListing Controller
 *
 * @property \App\Model\Table\AmpEmployeesListingTable $AmpEmployeesListing
 *
 * @method \App\Model\Entity\AmpEmployeesListing[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AmpEmployeesListingController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Emails']
        ];
        $ampEmployeesListing = $this->paginate($this->AmpEmployeesListing);

        $this->set(compact('ampEmployeesListing'));
    }

    /**
     * View method
     *
     * @param string|null $id Amp Employees Listing id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ampEmployeesListing = $this->AmpEmployeesListing->get($id, [
            'contain' => ['Emails']
        ]);

        $this->set('ampEmployeesListing', $ampEmployeesListing);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ampEmployeesListing = $this->AmpEmployeesListing->newEntity();
        if ($this->request->is('post')) {
            $ampEmployeesListing = $this->AmpEmployeesListing->patchEntity($ampEmployeesListing, $this->request->getData());
            if ($this->AmpEmployeesListing->save($ampEmployeesListing)) {
                $this->Flash->success(__('The amp employees listing has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp employees listing could not be saved. Please, try again.'));
        }
        $emails = $this->AmpEmployeesListing->Emails->find('list', ['limit' => 200]);
        $this->set(compact('ampEmployeesListing', 'emails'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Amp Employees Listing id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ampEmployeesListing = $this->AmpEmployeesListing->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ampEmployeesListing = $this->AmpEmployeesListing->patchEntity($ampEmployeesListing, $this->request->getData());
            if ($this->AmpEmployeesListing->save($ampEmployeesListing)) {
                $this->Flash->success(__('The amp employees listing has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp employees listing could not be saved. Please, try again.'));
        }
        $emails = $this->AmpEmployeesListing->Emails->find('list', ['limit' => 200]);
        $this->set(compact('ampEmployeesListing', 'emails'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Amp Employees Listing id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ampEmployeesListing = $this->AmpEmployeesListing->get($id);
        if ($this->AmpEmployeesListing->delete($ampEmployeesListing)) {
            $this->Flash->success(__('The amp employees listing has been deleted.'));
        } else {
            $this->Flash->error(__('The amp employees listing could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
