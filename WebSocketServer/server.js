var app = require("http").createServer();

const io = require("socket.io")(app, {
  allowEIO3: true,
  cors: {
    origin: ["http://localhost:3000", "http://localhost:8000"],
    methods: ["GET", "POST"],
    allowedHeaders: ["my-custom-header"],
    credentials: true
  }
});

app.listen(8080, function() {
  console.log("listening on *:8080");
});


const { emit } = require("process");
var LoggedUsers = require("./loggedusers.js");
let loggedUsers = new LoggedUsers();

io.on("connection", function(socket) {

  console.log("client has connected (socket ID = " + socket.id + ")");

  socket.on("refresh", function(user){

    if(user){

      let loggedUser = loggedUsers.userInfoByID(user.id);
  
      if(loggedUser){
        let message = `Chaning socket id of user ${user.id} from ${loggedUser.socketID} to ${socket.id}`; 
        console.log(message);
        loggedUser.socketID = socket.id;
      }
      
    }

  });

  socket.on("user_enter", function(user) {
    if (user) {
      loggedUsers.addUserInfo(user, socket.id);
      console.log(`User with email ${user.email} logged in`);
      console.log("Users map -> ", loggedUsers);
    }
  });

  socket.on("user_exit", function(user) {
    if(loggedUsers.userInfoByID(user.id)){
      console.log(`User with email ${user.email} logged out`);
      loggedUsers.removeUserInfoByID(user.id);
      console.log("Users map -> ", loggedUsers);
    }
  }); 

  socket.on("auction_bidded", function(auction, last_user_id){
      
    console.log(`User ${auction.last_bid_user_id} bidded ${auction.name}`);
    let owner = loggedUsers.userInfoByID(auction.owner_id);
    let last_user = loggedUsers.userInfoByID(last_user_id);

    console.log("Owner", owner);
    console.log("Last user", last_user);

    const message = `The auction ${auction.name} has been bidded with a higher value of ${auction.last_bid_price}`;

    socket.broadcast.emit("auction_bidded", { auction: auction });

    if(owner){
      io.to(owner.socketID).emit("auction_bidded", {message: message});
    }

    if(last_user){
      io.to(last_user.socketID).emit("auction_bidded", {message: message});
    }

  });

  socket.on("auction_closed", function(auction){

    console.log(`User ${auction.owner_id} closed auction "${auction.name}"`);
    console.log(`User ${auction.last_bid_user_id} paid owner ${auction.last_bid_price}`);
    
    let last_user = loggedUsers.userInfoByID(auction.last_bid_user_id);

    if(last_user){
      io.to(last_user.socketID).emit("auction_closed", {
        auction: auction, 
        message: `Auction "${auction.name}" was closed. ${auction.last_bid_price}VND were transfered from your account`});
    }

  });

});
