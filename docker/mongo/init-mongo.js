db.createUser(
    {
        user: "user",
        pwd: "test",
        roles: [
            {
                role: "readWrite",
                db: "test"
            }
        ]
    }
);