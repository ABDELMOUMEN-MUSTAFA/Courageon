<?php

namespace App\Controllers;

use App\Models\{
    Formation,
    Inscription,
    Formateur,
    Notification
};

class StripeController
{
	private $formationModel;
	private $inscriptionModel;

	public function __construct()
	{
		if (!auth() || !session('user')->get()->type === 'etudiant') {
			return redirect('user/login');
		}

		if(!session('user')->get()->email_verified_at) {
			return redirect('user/verify');
		}

		$this->formationModel = new Formation;
		$this->inscriptionModel = new Inscription;
	}

	public function payment($request, $id_formation = null)
	{
		if($request->getMethod() !== 'GET'){
			return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
		}

		$formation = $this->formationModel->select($id_formation, [
            'id_formation', 
            'prix', 
            'nom',
            'id_formateur',
			'etat',
        ]);

		if (!$formation || $formation->etat === 'private') {
			// cette formation n'existe pas ou prive
			return view('errors/page_404');
		}

		$inscription = $this->inscriptionModel->checkIfAlready(session('user')->get()->id_etudiant, $id_formation);
		if ($inscription) {
			if ($inscription->payment_state === "approved") {
				// vous etes deja inscrit dans cette formation
				return redirect('etudiant/formation/'.$id_formation);	
			}

            // redirect to stripe page to make payment, because the order already created
            http_response_code(303);
            header('location: ' . $inscription->approval_url);
            exit;
		}

        \Stripe\Stripe::setApiKey($_ENV['SECRET_KEY_STRIPE']);
        $payment_id = bin2hex(random_bytes(20)).uniqid();
        $checkout_session = \Stripe\Checkout\Session::create([
            "mode" => "payment",
            "success_url" => URLROOT . "/stripe/success/{$id_formation}?paymentId={$payment_id}",
            "cancel_url" => URLROOT . "/stripe/cancel/{$id_formation}?paymentId={$payment_id}",
            "locale" => "fr",
            "line_items" => [
                [
                    "quantity" => 1,
                    "price_data" => [
                        "currency" => "usd",
                        "unit_amount" => $formation->prix * 100,
                        "product_data" => [
                            "name" => $formation->nom
                        ]
                    ]
                ]
            ] 
        ]);

		$inscription = [
			"id_formation" => $formation->id_formation,
			"id_etudiant" => session('user')->get()->id_etudiant,
			"id_formateur" => $formation->id_formateur,
			"prix" => $formation->prix,
			"transaction_info" => "{}",
			"payment_id" => $payment_id,
			"payment_state" => "created",
			"date_inscription" => date('Y-m-d H:i:s'),
			"approval_url" => $checkout_session->url
		];

		$this->inscriptionModel->create($inscription);

		// redirect to stripe page to make payment
        http_response_code(303);
		header('location: ' . $checkout_session->url);
		exit;
	}

	public function success($request, $id_formation = null)
	{
		if($request->getMethod() !== 'GET'){
			return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
		}

        if (!auth() || !session('user')->get()->type === 'etudiant') {
			return view('errors/page_404');
		}
        
		$inscription = $this->inscriptionModel->wherePaymentID($_GET['paymentId'] ?? "", session('user')->get()->id_etudiant);

		if (!$inscription) {
			return view('errors/page_404');
		}

        $formation = $this->formationModel->select($id_formation, ['slug', 'nom', 'id_formateur', 'prix']);
        $this->inscriptionModel->updatePaymentState($_GET['paymentId'], "approved");
		$formateurModel = new Formateur;
		$formateurProfit = (100 - $_ENV['PLATFORM_PROFIL']) / 100;
		$formateurModel->updateBalance($formation->id_formateur, $formation->prix * $formateurProfit);
		$notificationModel = new Notification;
		$notificationModel->create([
			'content' => "Joined Your Course <strong>{$formation->nom}</strong>",
			'sender_id' => session('user')->get()->id_etudiant,
			'recipient_id' => $formation->id_formateur
		]);
		return view('payments/paymentSuccess', ["id_formation" => $id_formation]);
	}

	public function cancel($request, $id_formation = null)
	{
		if($request->getMethod() !== 'GET'){
			return \App\Libraries\Response::json(null, 405, "Method Not Allowed");
		}

        if (!auth() || !session('user')->get()->type === 'etudiant') {
			return view('errors/page_404');
		}

		$formation = $this->formationModel->select($id_formation, ['slug']);
        if (!$formation) {
			return view('errors/page_404');
		}

        $inscription = $this->inscriptionModel->wherePaymentID($_GET['paymentId'] ?? "", session('user')->get()->id_etudiant);
        if($inscription){
            return view('payments/paymentCancel', ['slug' => $formation->slug]);
        }
		return redirect('courses/' . $formation->slug);
	}
}
