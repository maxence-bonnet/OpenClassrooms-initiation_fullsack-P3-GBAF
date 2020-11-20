<?php
	session_start();
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" href="style.css" />
		<title>Acteur</title>
	</head>
	<body>
	<?php include("../includes/inc_header.php"); ?>
	<div class ="content actor_content">
		<?php
		if(isset($_GET['act']) AND isset($_SESSION['username'])) // si connecté
		{
			$username = htmlspecialchars($_SESSION['username']);
			$actor = htmlspecialchars((int)$_GET['act']);
			try
			{
			$db = new PDO('mysql:host=localhost;dbname=gbaf;charset=utf8', 'root', '');
			}
			catch (Exception $e)
			{
			        die('Erreur : ' . $e->getMessage());
			}			
			$result = $db->prepare('SELECT id_actor FROM actor WHERE id_actor = :actor');
			$result->execute(array('actor' => $actor));
			$data = $result->fetch();
			if(!$data)// si le $_GET['act'] est incorrect / inexistant
			{
				?>
				<!-- choisir un affichage par défaut si le $_GET['act'] existe mais n'est pas valide :
					afficher la liste complète des acteurs comme à l'accueil ?-->
				<?php
				header('Location: accueil.php');
			}
			else // $_GET['act'] renvoie a une valeur existante dans les ID des acteurs
			{
				//affiche le contenu de l'acteur act=x, les likes/dislike et les commentaires associés 
				$result = $db->prepare('SELECT * FROM actor WHERE id_actor = :actor');
				$result->execute(array('actor' => $actor));
				$data = $result->fetch();
				$result->closeCursor();
				?>
				<div class="actor_full">
			    	<div class="actor_full_logo"><img src="logos/<?php echo $data['logo']; ?>" alt="logo <?php echo $data['actor']; ?>"></div>
			    		<div class="actor_full_description">
				    		<h3><?php echo $data['actor']; ?></h3>
				    		<p><?php echo nl2br($data['description']); ?></p>				    		
				    	</div>
				    </div>
				    	<div class="actor_like_management">
				    		<a href="acteur.php?act=<?php echo $actor; ?>&amp;add=1#new_post">Ajouter un commentaire public</a>
				    		<div class="actor_like">
				    			<?php // Gestion like/dislike, on affichera à la fois le nombre de like et le nombre de dislike, plus explicite qu'une somme des deux.
				    				// 1) les likes
									$result = $db->prepare('SELECT COUNT(*) AS like_number FROM vote WHERE id_actor = :actor AND vote = :like_');
									$result->execute(array('actor' => $actor, 'like_' => 'like'));
									$data1 = $result->fetch();
									$result->closeCursor();
									$like_number = $data1['like_number'];
									// 2) les dislikes
									$result = $db->prepare('SELECT COUNT(*) AS dislike_number FROM vote WHERE id_actor = :actor AND vote = :dislike_');
									$result->execute(array('actor' => $actor, 'dislike_' => 'dislike'));
									$data2 = $result->fetch();
									$result->closeCursor();
									$dislike_number = $data2['dislike_number'];
									// 3) S'il n'y pas encore de like ou dislike on paramètre les variables à 0
									if(!$data1)
									{
										$like_number = 0;
									}
									if(!$data2)
									{
										$dislike_number = 0;
									}
									// 4) Affichage personnalisé si l'utilisateur a déjà mis un like / dislike 
										$result = $db->prepare('SELECT account.id_user, username, vote.id_user, id_actor, vote 
																FROM account
																INNER JOIN vote
																ON account.id_user = vote.id_user
																WHERE username = :username
																AND id_actor = :actor');
										$result->execute(array('username' => $username, 'actor' => $actor));
										$data3 = $result->fetch();
										$result->closeCursor();
										if(!$data3)// s'il n'y a aucun lien entre l'utilisateur et cet ateur pour le moment
										{
											$show = false;
										}
										elseif(isset($data3['vote']))// il y a soit un like soit un dislike /isset($data3['vote']) AND $data3['vote'] != 0
										{
											$vote = htmlspecialchars($data3['vote']);
											if($vote == 'like')
											{
												$show = 'Vous aimez ce partenaire';
											}
											if($vote == 'dislike')
											{
												$show = 'Vous n\'aimez pas ce partenaire';
											}
										}
										else // pas de raison de que ça arrive mais au cas où
										{
										$show = false;
										}
									// Bonus veut afficher en infobulle la liste des personnes qui aiment / n'aiment pas le partenaire
									// 1) Ceux qui like
									$result = $db->prepare('SELECT account.id_user, nom, prenom, vote.id_user, id_actor, vote 
															FROM vote
															INNER JOIN account
															ON account.id_user = vote.id_user
															WHERE id_actor = :actor
															AND vote = :like_');
									$result->execute(array('actor' => $actor, 'like_' => 'like'));
									while($data4 = $result->fetch())
									{
										$like_list[] = $data4['nom'] . ' ' . $data4['prenom'] ;
									}																									
									$result->closeCursor();
									// 2) Ceux qui dislike
									$result = $db->prepare('SELECT account.id_user, nom, prenom, vote.id_user, id_actor, vote 
															FROM account
															INNER JOIN vote
															ON account.id_user = vote.id_user
															WHERE id_actor = :actor
															AND vote = :dislike_');
									$result->execute(array('actor' => $actor, 'dislike_' => 'dislike'));
									while($data5 = $result->fetch())
									{
										$dislike_list[] = $data5['nom'] . ' ' . $data5['prenom'] ;
									}									
									$result->closeCursor();												
								?>	    			
				    			<a href="../traitement/trait_like.php?act=<?php echo $actor ?>&amp;like=1" title="
				    			<?php 
				    			if(!empty($like_list))
				    			{
					    			foreach($like_list as $name)
					    			{
					    				echo $name . '&#013;';
					    			}					    				
				    			}	
				    			?>">
				    				<?php echo '(' . $like_number . ') ' ?>like</a> / 
				    			<a href="../traitement/trait_like.php?act=<?php echo $actor ?>&amp;like=2" title="
				    			<?php
				    			if(!empty($dislike_list))
				    			{
					    			foreach($dislike_list as $name)
					    			{
					    				echo $name . '&#013;';
					    			}					    				
				    			}			    			
				    			?>">
				    				<?php echo '(' . $dislike_number . ') ' ?>dislike</a>
				    		</div>
				    		<?php 
				    				if($show) // On affiche si l'utilisateur aime ou non le partenaire avec possibilité de réinitialiser
				    				{
										echo '<div class="actor_like_mention"><p>' . $show . '. |  </p>'; ?>
										<a href="../traitement/trait_like.php?act=<?php echo $actor ?>&amp;like=3">Réinitialiser</a></div>
										<?php
				    				}
				    		?>			    	
				    	</div>
					<div class="post_section">
						<h4>Commentaires :</h4>
					<?php
					// On vérifie qu'il existe des commentaires pour cet acteur
					$result = $db->prepare('SELECT id_actor FROM post WHERE id_actor = :actor');
					$result->execute(array('actor' => $actor));
					$data = $result->fetch();
					$result->closeCursor();
					if(!$data)
					{
						?>
						<p> Pas encore de commentaire publié pour ce partenaire.</p>
						<?php
					}
					else
					{
					$result = $db->prepare('SELECT account.id_user, nom, prenom, photo, post.id_user, id_actor, date_add, post 
											FROM post
											INNER JOIN account
											ON account.id_user = post.id_user
											WHERE id_actor = :actor
											ORDER BY date_add');
					$result->execute(array('actor' => $actor));
					while($data = $result->fetch())
					{ // mettre htmlspecialchars
						$nom = $data['nom'];
						$prenom = $data['prenom'];
						$date = preg_replace("#([0-9]{4})-([0-9]{2})-([0-9]{2})#","Le $3/$2/$1",$data['date_add']);
						$post = $data['post'];
						$photo = $data['photo']
						?>
							<div class="post">
								<div class="post_photo"><img src="uploads/<?php echo $photo ; ?>" alt="photo"/><p> Nom : <?php echo $nom; ?></p></div>
								<p> Prenom : <?php echo $prenom; ?></p>
								<p> Date : <?php echo $date; ?></p>
								<p> Commentaire : <br/><?php echo nl2br($post); ?></p>
							</div>
						<?php
					}
					$result->closeCursor();
				}
			}
			?>
				</div>
			<?php
			if(isset($_GET['add']) AND $_GET['add'] == 1)
			{
				?>
				<form class="add_post" action="../traitement/trait_commentaire.php?act=<?php echo $actor; ?>" method="post">
					<label for="new_post">Votre commentaire : </label><textarea name="new_post" id="new_post"></textarea>
					<input type="submit" name="new_post_submit" value="Publier"/>
				</form>
				<?php
			}
		
		}
		else
		{
			// utilisateur non connecté renvoyé page accueil (connexion)
			header('Location: accueil.php');
		}
		?>
		</div>
		<?php include("../includes/inc_footer.php"); ?>
	</body>
</html>