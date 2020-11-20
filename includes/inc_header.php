<div class="header_content">
	<!-- logo GBAF -->
	<div class="logo_gbaf">
		<a href="accueil.php"><img src="../pages/logos/gbaf.png" title="GBAF"alt="GBAF logo"/></a>
	</div>
	<!-- Photo & Nom + Prenom -->
	<?php
		if(isset($_SESSION['last_name']) AND isset($_SESSION['first_name']) AND (isset($_SESSION['username']))) //si session active
		{
			try
			{
			$db = new PDO('mysql:host=localhost;dbname=gbaf;charset=utf8', 'root', '');
			}
			catch (Exception $e)
			{
			        die('Erreur : ' . $e->getMessage());
			}
			$nom = htmlspecialchars($_SESSION['last_name']);
			$prenom = htmlspecialchars($_SESSION['first_name']);
			$username = htmlspecialchars($_SESSION['username']);
			$result = $db->prepare('SELECT photo FROM account WHERE username = :username');
			$result->execute(array('username' => $username));
			$data = $result->fetch();
			$photo = htmlspecialchars($data['photo']); //éléments par défaut
			?>
			<div class="user_ref">
				<!-- avatar -->
				<div class="user_photo">
					<a href="profil.php"><img src="uploads/<?php echo $photo ; ?>" alt="Ma photo de profil"/></a>
				</div>
				<!-- nom + prenom -->
				<div class="user_name">
					<a href="profil.php"><p><?php echo $nom . ' ' . $prenom; ?></a>
				</div>
				<!-- bouton déconnexion -->
				<form class="deconnection_form" action="../traitement/trait_deconnexion.php" method="post"><input type="submit" value="deconnexion"/></form></li>				
			</div>
			<?php
		}
		else // pas de session
		{
			?>
			<div class="inscription_link">
				<a href="inscription.php">S'inscrire</a><p>/</p><a href="accueil.php">Se connecter</a>
			</div>
			<?php
		}
	?>
</div>